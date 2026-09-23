<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ParticipationController as AdminParticipationController;
use App\Http\Controllers\DisplayController;
use App\Http\Controllers\ExploreController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ParticipationController;
use Illuminate\Support\Facades\Route;

// ── الواجهة العامة ─────────────────────────────────────────
Route::get('/', HomeController::class)->name('home');

Route::get('/participate', [ParticipationController::class, 'create'])->name('participate');
Route::post('/participate', [ParticipationController::class, 'store'])
    ->middleware('throttle:participate')->name('participate.store');
Route::get('/participate/done', [ParticipationController::class, 'success'])->name('participate.success');

Route::get('/explore', ExploreController::class)->name('explore');

Route::get('/display', [DisplayController::class, 'show'])->name('display');
Route::get('/display/feed', [DisplayController::class, 'feed'])->middleware('throttle:feed')->name('display.feed');

// ── لوحة التحكم ────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'create'])->name('login');
        Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:login')->name('login.store');
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

        Route::get('/participations', [AdminParticipationController::class, 'index'])->name('participations.index');
        Route::get('/participations/export', [AdminParticipationController::class, 'export'])->name('participations.export');
        Route::post('/participations/bulk', [AdminParticipationController::class, 'bulk'])->name('participations.bulk');
        Route::get('/participations/{participation}', [AdminParticipationController::class, 'show'])->name('participations.show');
        Route::patch('/participations/{participation}/status', [AdminParticipationController::class, 'updateStatus'])->name('participations.status');
        Route::delete('/participations/{participation}', [AdminParticipationController::class, 'destroy'])->name('participations.destroy');
    });
});
