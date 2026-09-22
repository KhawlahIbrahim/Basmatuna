<?php

namespace App\Services;

use App\Enums\ParticipationStatus;
use App\Models\Participation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StatsService
{
    private const PUBLIC_KEY = 'basmah.stats.public.v1';

    public static function flush(): void
    {
        Cache::forget(self::PUBLIC_KEY);
        Cache::forget('basmah.display.feed');
    }

    /**
     * إحصائيات حيّة من المشاركات المعتمدة فقط.
     *
     * @return array{participants:int, departments:array<int,string>, department_count:int,
     *               words:array<string,int>, word_count:int, top_word:?string}
     */
    public function publicStats(): array
    {
        return Cache::remember(self::PUBLIC_KEY, config('basmah.stats_cache_seconds', 60), function () {
            $words = Participation::approved()
                ->select('selected_word', DB::raw('COUNT(*) AS total'))
                ->groupBy('selected_word')
                ->orderByDesc('total')
                ->orderBy('selected_word')
                ->get()
                ->mapWithKeys(fn ($row) => [(string) $row->selected_word => (int) $row->total])
                ->all();

            $departments = Participation::approved()
                ->distinct()->orderBy('department')->pluck('department')->all();

            return [
                'participants' => Participation::approved()->count(),
                'departments' => $departments,
                'department_count' => count($departments),
                'words' => $words,
                'word_count' => count($words),
                'top_word' => array_key_first($words),
            ];
        });
    }

    /**
     * تحويل تكرارات الكلمات إلى سحابة: وزن من 1 إلى 5، والأكبر في المنتصف.
     *
     * @param  array<string,int>  $words
     * @return array<int, array{word:string, count:int, weight:int}>
     */
    public function cloud(array $words): array
    {
        if ($words === []) {
            return [];
        }

        $max = max($words);
        $min = min($words);
        $items = [];
        foreach ($words as $word => $count) {
            $weight = $max === $min ? 3 : 1 + (int) round(4 * ($count - $min) / ($max - $min));
            $items[] = ['word' => (string) $word, 'count' => $count, 'weight' => $weight];
        }

        // ترتيب تنازلي ثم توزيع تبادلي حتى تتوسط الكلمات الأكبر السحابة
        $arranged = [];
        foreach ($items as $i => $item) {
            $i % 2 === 0 ? array_push($arranged, $item) : array_unshift($arranged, $item);
        }

        return $arranged;
    }

    public function adminStats(): array
    {
        $byStatus = Participation::query()
            ->select('status', DB::raw('COUNT(*) AS total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($v) => (int) $v);

        $topWords = Participation::query()
            ->select('selected_word', DB::raw('COUNT(*) AS total'))
            ->groupBy('selected_word')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        return [
            'total' => $byStatus->sum(),
            'pending' => $byStatus[ParticipationStatus::Pending->value] ?? 0,
            'approved' => $byStatus[ParticipationStatus::Approved->value] ?? 0,
            'hidden' => $byStatus[ParticipationStatus::Hidden->value] ?? 0,
            'departments' => Participation::query()->distinct()->count('department'),
            'top_words' => $topWords,
            'demo' => Participation::where('is_demo', true)->count(),
        ];
    }
}
