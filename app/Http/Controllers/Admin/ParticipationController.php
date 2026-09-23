<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ParticipationStatus;
use App\Http\Controllers\Controller;
use App\Models\Participation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ParticipationController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(ParticipationStatus::values())],
            'q' => ['nullable', 'string', 'max:60'],
        ]);

        $query = Participation::query()->status($filters['status'] ?? null);

        if ($q = trim($filters['q'] ?? '')) {
            $like = '%'.addcslashes($q, '%_\\').'%';
            $query->where(fn ($w) => $w->where('full_name', 'like', $like)
                ->orWhere('department', 'like', $like)
                ->orWhere('message', 'like', $like));
        }

        $counts = Participation::query()->select('status', DB::raw('COUNT(*) AS total'))
            ->groupBy('status')->pluck('total', 'status');

        return view('admin.participations.index', [
            'items' => $query->latest()->paginate(20)->withQueryString(),
            'status' => $filters['status'] ?? null,
            'q' => $q,
            'counts' => $counts,
        ]);
    }

    public function show(Participation $participation): View
    {
        return view('admin.participations.show', ['p' => $participation->load('reviewer')]);
    }

    public function updateStatus(Request $request, Participation $participation): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(ParticipationStatus::values())]]);
        $this->applyStatus([$participation->id], $data['status'], $request->user()->id);

        return back()->with('status', 'تم تحديث حالة مشاركة «'.$participation->full_name.'».');
    }

    public function bulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'max:200'],
            'ids.*' => ['integer'],
            'action' => ['required', Rule::in([...ParticipationStatus::values(), 'delete'])],
        ], ['ids.required' => 'اختر مشاركة واحدة على الأقل.']);

        if ($data['action'] === 'delete') {
            Participation::whereIn('id', $data['ids'])->get()->each->delete();
            $msg = 'تم حذف المشاركات المحددة.';
        } else {
            $this->applyStatus($data['ids'], $data['action'], $request->user()->id);
            $msg = 'تم تحديث المشاركات المحددة إلى: '.ParticipationStatus::from($data['action'])->label().'.';
        }

        return back()->with('status', $msg);
    }

    public function destroy(Participation $participation): RedirectResponse
    {
        $name = $participation->full_name;
        $participation->delete();

        return redirect()->route('admin.participations.index')->with('status', 'تم حذف مشاركة «'.$name.'».');
    }

    public function export(): StreamedResponse
    {
        $filename = 'basmatuna-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM ليقرأ Excel العربية بشكل صحيح
            fputcsv($out, ['#', 'الاسم', 'القسم', 'الكلمة', 'العبارة', 'التاريخ', 'الحالة', 'تجريبية']);

            Participation::query()->orderBy('id')->chunkById(500, function ($rows) use ($out) {
                foreach ($rows as $p) {
                    fputcsv($out, array_map([$this, 'csvSafe'], [
                        $p->id,
                        $p->full_name,
                        $p->department,
                        $p->selected_word,
                        $p->message,
                        $p->created_at?->timezone(config('app.timezone'))->format('Y-m-d H:i'),
                        $p->status->label(),
                        $p->is_demo ? 'نعم' : 'لا',
                    ]));
                }
            });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** يمنع حقن الصيغ في Excel (CSV Injection) */
    public function csvSafe(mixed $value): string
    {
        $value = (string) $value;

        return preg_match('/^[=+\-@\t\r]/u', $value) ? "'".$value : $value;
    }

    /** @param array<int,int> $ids */
    private function applyStatus(array $ids, string $status, int $userId): void
    {
        // تحديث عبر النموذج لتفعيل تفريغ ذاكرة الإحصائيات
        Participation::whereIn('id', $ids)->get()->each(function (Participation $p) use ($status, $userId) {
            $p->status = ParticipationStatus::from($status);
            $p->reviewed_at = now();
            $p->reviewed_by = $userId;
            $p->save();
        });
        Cache::forget('basmah.display.feed');
    }
}
