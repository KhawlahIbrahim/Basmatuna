<?php

namespace App\Http\Controllers;

use App\Models\Participation;
use App\Services\StatsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DisplayController extends Controller
{
    public function show(Request $request): View
    {
        $intervals = config('basmah.display.intervals');
        $interval = (int) $request->query('interval', config('basmah.display.default_interval'));

        return view('pages.display', [
            'intervals' => $intervals,
            'interval' => in_array($interval, $intervals, true) ? $interval : config('basmah.display.default_interval'),
        ]);
    }

    public function feed(StatsService $stats): JsonResponse
    {
        $items = Cache::remember('basmah.display.feed', 30, fn () => Participation::approved()
            ->latest()
            ->limit(config('basmah.display.feed_limit'))
            ->get()
            ->map->toPublicArray()
            ->values()
            ->all());

        return response()->json([
            'count' => $stats->publicStats()['participants'],
            'items' => $items,
        ])->header('Cache-Control', 'no-store');
    }
}
