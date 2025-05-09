<?php

use App\Http\Controllers\API\V1\RegisterController;
use App\Http\Controllers\API\V1\WalletController;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
Route::prefix('v1')->group(function (){

    Route::prefix('user')->group(function () {
        Route::prefix('register')->group(function () {
            Route::post('send-otp', [RegisterController::class, 'sendOtp']);
            Route::post('verify-otp', [RegisterController::class, 'verifyOtp']);
            Route::post('complete-profile', [RegisterController::class, 'completeProfile']);
        });
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('wallet/deposit', [WalletController::class, 'deposit']);
        Route::get('wallet/transactions', [WalletController::class, 'transactions']);
        Route::post('wallet/transfer', [WalletController::class, 'transfer']);
        Route::post('wallet/withdraw', [WalletController::class, 'requestWithdraw']);
        Route::get('wallet/balance', [WalletController::class, 'balance']);
    });

    // Admin
    Route::middleware(['auth:sanctum', 'can:isAdmin'])->post(
        'wallet/withdraw-review/{withdraw}',
        [WalletController::class, 'reviewWithdrawRequest']
    );

    Route::post('/logout', function (Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'با موفقیت خارج شدید']);
    })->middleware('auth:sanctum');

});

