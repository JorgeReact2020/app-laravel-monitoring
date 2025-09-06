<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\RebootController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Public webhook endpoint (no auth required)




// Authenticated routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard routes
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/incidents', [DashboardController::class, 'incidents'])->name('dashboard.incidents');
    Route::get('/dashboard/reboot-logs', [DashboardController::class, 'rebootLogs'])->name('dashboard.reboot-logs');
    Route::get('/dashboard/analytics', [DashboardController::class, 'analytics'])->name('dashboard.analytics');

    // Sites management
    Route::resource('sites', SiteController::class);
    Route::post('/sites/{site}/test-connection', [SiteController::class, 'testConnection'])
        ->name('sites.test-connection');
/*     Route::get('/sites/{site}/edit', [SiteController::class, 'edit'])
        ->name('sites.edit'); */
    Route::post('/sites/{site}', [SiteController::class, 'update'])
        ->name('sites.update');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
