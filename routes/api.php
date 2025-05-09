<?php

use App\Http\Controllers\API\V1\RegisterController;
use Illuminate\Support\Facades\Route;

Route::prefix('/user')->group(function () {
    Route::prefix('register')->group(function () {
        Route::post('send-otp', [RegisterController::class, 'sendOtp']);
        Route::post('verify-otp', [RegisterController::class, 'verifyOtp']);
        Route::post('complete-profile', [RegisterController::class, 'completeProfile']);
    });
});
