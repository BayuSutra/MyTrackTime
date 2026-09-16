<?php

use App\Http\Controllers\Api\TrackingController;
use App\Http\Controllers\Api\CommandController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // ESP32 API endpoints
    Route::post('/tracking/update', [TrackingController::class, 'updateLocation']);
    Route::get('/tracking/{itemCode}', [TrackingController::class, 'getLocation']);
});

Route::prefix('v1')->group(function () {
    // GPS Webhook
    Route::post('/gps/webhook', [TrackingController::class, 'webhook']);
    Route::get('/gps/device/{deviceId}', [TrackingController::class, 'getDeviceData']);
    
    // MQTT Commands
    Route::post('/mqtt/send-command', [CommandController::class, 'sendCommand']);
    Route::get('/mqtt/device/{deviceId}/status', [CommandController::class, 'getDeviceStatus']);
});
