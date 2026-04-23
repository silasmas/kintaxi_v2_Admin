<?php

use App\Http\Controllers\LiveRideTrackingFeedController;
use App\Http\Controllers\SmileIdCallbackController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/webhooks/smile-id', SmileIdCallbackController::class)
    ->name('webhooks.smile-id');

Route::middleware('auth')->group(function (): void {
    Route::get('/admin/live-ride-tracking/feed', LiveRideTrackingFeedController::class)
        ->name('admin.live-ride-tracking.feed');
});
