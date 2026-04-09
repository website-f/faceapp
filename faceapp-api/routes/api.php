<?php

use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\EnrollmentController;
use Illuminate\Support\Facades\Route;

Route::get('/device/status', [DeviceController::class, 'status']);

Route::post('/enrollments', [EnrollmentController::class, 'store']);
Route::get('/enrollments/{enrollment:public_id}', [EnrollmentController::class, 'show']);
