<?php

use App\Http\Controllers\AlertController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

// Redirect root to login
// Auth Routes
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Redirect /login to /
Route::get('/login', function () {
    return redirect('/');
});

// Protected Routes
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/realtime', [DashboardController::class, 'getRealtimeData'])->name('dashboard.realtime');

    // Alerts
    Route::get('/alerts', [AlertController::class, 'index'])->name('alerts.index');
    Route::get('/alerts/{id}', [AlertController::class, 'show'])->name('alerts.show');
    Route::post('/alerts/{id}/read', [AlertController::class, 'markAsRead'])->name('alerts.read');
    Route::post('/alerts/read-all', [AlertController::class, 'markAllAsRead'])->name('alerts.readAll');

    // Devices
    Route::get('/devices', [DeviceController::class, 'index'])->name('devices.index');
    Route::put('/devices/{device}', [DeviceController::class, 'update'])->name('devices.update');

    // History
    Route::get('/history', [HistoryController::class, 'index'])->name('history.index');

    // Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
});
