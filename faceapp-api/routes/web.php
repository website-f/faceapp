<?php

use App\Http\Controllers\DeviceMonitorController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('devices.monitor.index');
});

Route::get('/devices', [DeviceMonitorController::class, 'index'])->name('devices.monitor.index');
Route::post('/devices/configure-callbacks', [DeviceMonitorController::class, 'configureCallbacks'])
    ->name('devices.monitor.configure');
