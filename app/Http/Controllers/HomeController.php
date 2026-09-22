<?php

namespace App\Http\Controllers;

use App\Services\StatsService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(StatsService $stats): View
    {
        return view('pages.home', [
            'participants' => $stats->publicStats()['participants'],
        ]);
    }
}
