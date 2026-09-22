<?php

namespace App\Http\Controllers;

use App\Models\Participation;
use App\Services\StatsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExploreController extends Controller
{
    public function __invoke(Request $request, StatsService $statsService): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:60'],
            'department' => ['nullable', 'string', 'max:80'],
            'word' => ['nullable', 'string', 'max:30'],
        ]);

        $stats = $statsService->publicStats();

        $query = Participation::approved();

        if ($q = trim($filters['q'] ?? '')) {
            $like = '%'.addcslashes($q, '%_\\').'%';
            $query->where('full_name', 'like', $like);
        }
        if (($dept = $filters['department'] ?? null) && in_array($dept, $stats['departments'], true)) {
            $query->where('department', $dept);
        } else {
            $dept = null;
        }
        if (($word = $filters['word'] ?? null) && array_key_exists($word, $stats['words'])) {
            $query->where('selected_word', $word);
        } else {
            $word = null;
        }

        return view('pages.explore', [
            'items' => $query->latest()->paginate(24)->withQueryString(),
            'stats' => $stats,
            'cloud' => $statsService->cloud($stats['words']),
            'active' => ['q' => $q ?? '', 'department' => $dept, 'word' => $word],
            'filtered' => ($q ?? '') !== '' || $dept || $word,
        ]);
    }
}
