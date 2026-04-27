<?php

use App\Http\Controllers\Api\LicenseApiController;
use Illuminate\Support\Facades\Route;

// License API v1
Route::prefix('v1')->middleware('throttle:30,1')->group(function () {
    Route::post('/activate', [LicenseApiController::class, 'activate']);
    Route::post('/heartbeat', [LicenseApiController::class, 'heartbeat']);
    Route::post('/validate', [LicenseApiController::class, 'validate']);
});
