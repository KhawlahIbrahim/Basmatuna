<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ParticipationStatus;
use App\Http\Controllers\Controller;
use App\Models\Participation;
use App\Services\StatsService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(StatsService $stats): View
    {
        return view('admin.dashboard', [
            'stats' => $stats->adminStats(),
            'latestPending' => Participation::where('status', ParticipationStatus::Pending->value)
                ->latest()->limit(6)->get(),
        ]);
    }
}
