<?php

use App\Http\Controllers\SensorApiController;
use Illuminate\Support\Facades\Route;

// API Endpoints for NodeMCU ESP8266
Route::post('/sensor', [SensorApiController::class, 'store'])->name('api.sensor.store');
Route::get('/sensor/latest', [SensorApiController::class, 'latest'])->name('api.sensor.latest');
Route::get('/sensor/history', [SensorApiController::class, 'history'])->name('api.sensor.history');
