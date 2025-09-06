<?php

use App\Http\Controllers\RebootController;
use App\Http\Controllers\WebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/webhook/uptime-kuma', [WebhookController::class, 'uptimeKuma'])
    ->name('webhook.uptime-kuma');


// Reboot routes with signed URLs (no auth required but signature validated)
Route::prefix('reboot')->name('reboot.')->group(function () {
    Route::get('/site/{site}/incident/{incident}', [RebootController::class, 'show'])
        ->name('show');
    Route::post('/site/{site}/incident/{incident}', [RebootController::class, 'reboot'])
        ->name('execute');
    Route::get('/site/{site}/incident/{incident}/status/{rebootLog}', [RebootController::class, 'status'])
        ->name('status');
});
