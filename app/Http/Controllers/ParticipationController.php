<?php

namespace App\Http\Controllers;

use App\Enums\ParticipationStatus;
use App\Http\Requests\StoreParticipationRequest;
use App\Models\Participation;
use App\Services\ImageProcessor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ParticipationController extends Controller
{
    private const SESSION_KEY = 'basmah.submitted_id';

    public function create(): View
    {
        return view('pages.participate', [
            'open' => (bool) config('basmah.submissions_open'),
            'words' => config('basmah.words'),
            'departments' => config('basmah.departments'),
            'audienceTypes' => config('basmah.audience_types'),
        ]);
    }

    public function store(StoreParticipationRequest $request, ImageProcessor $images): RedirectResponse|JsonResponse
    {
        $imagePath = null;

        if ($request->hasFile('photo')) {
            try {
                $imagePath = $images->store($request->file('photo'));
            } catch (\Throwable $e) {
                Log::warning('Participation photo rejected', [
                    'reason' => $e->getMessage(),
                    'size' => $request->file('photo')->getSize(),
                    'client_mime' => $request->file('photo')->getClientMimeType(),
                ]);

                $message = 'تعذّر رفع الصورة. جرّب صورة أخرى.';

                if ($request->wantsJson()) {
                    return response()->json(['message' => $message, 'errors' => ['photo' => [$message]]], 422);
                }

                return back()->withInput()->withErrors(['photo' => $message]);
            }
        }

        $participation = Participation::create([
            'full_name' => $request->input('full_name'),
            'audience_type' => $request->input('audience_type'),
            'department' => $request->finalDepartment(),
            'selected_word' => $request->finalWord(),
            'is_custom_word' => $request->input('selected_word') === config('basmah.other_word'),
            'message' => $request->input('message'),
            'image_path' => $imagePath,
            'show_image' => $imagePath !== null && $request->boolean('show_image'),
            'status' => ParticipationStatus::Pending,
            'consent' => true,
            'consented_at' => now(),
        ]);

        $request->session()->put(self::SESSION_KEY, $participation->id);
        $request->session()->regenerateToken(); // يمنع إعادة الإرسال المكرر بنفس النموذج

        if ($request->wantsJson()) {
            return response()->json(['redirect' => route('participate.success')]);
        }

        return redirect()->route('participate.success');
    }

    /** صفحة النجاح تعرض بطاقة صاحب المشاركة فقط (من الجلسة) حتى وهي بانتظار الاعتماد */
    public function success(Request $request): View|RedirectResponse
    {
        $id = $request->session()->get(self::SESSION_KEY);
        $participation = $id ? Participation::find($id) : null;

        if (! $participation) {
            return redirect()->route('participate');
        }

        return view('pages.success', ['p' => $participation]);
    }
}
