<?php

use App\Http\Controllers\API\V1\AnnouncementController;
use App\Http\Controllers\API\V1\ChatController;
use App\Http\Controllers\API\V1\ProviderServiceController;
use App\Http\Controllers\API\V1\RegisterController;
use App\Http\Controllers\API\V1\ServiceController;
use App\Http\Controllers\API\V1\SupportMessageController;
use App\Http\Controllers\API\V1\SupportTicketController;
use App\Http\Controllers\API\V1\WalletController;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::prefix('user')->group(function () {
        Route::prefix('register')->group(function () {
            Route::post('send-otp', [RegisterController::class, 'sendOtp']);
            Route::post('resend-otp', [RegisterController::class, 'resendOtp']);
            Route::post('verify-otp', [RegisterController::class, 'verifyOtp']);
            Route::post('complete-profile', [RegisterController::class, 'completeProfile']);
        });
    });

    Route::middleware('auth:sanctum')->group(function () {

        // Wallet
        Route::prefix('wallet')->group(function () {
            Route::post('deposit', [WalletController::class, 'deposit']);
            Route::get('transactions', [WalletController::class, 'transactions']);
            Route::post('transfer', [WalletController::class, 'transfer']);
            Route::post('withdraw', [WalletController::class, 'requestWithdraw']);
            Route::get('balance', [WalletController::class, 'balance']);
        });

        // Announcements
        Route::get('announcements', [AnnouncementController::class, 'index']);

        // Support (User)
        Route::prefix('support')->group(function () {
            Route::post('tickets', [SupportTicketController::class, 'store']);
            Route::get('tickets', [SupportTicketController::class, 'index']);
            Route::get('tickets/{id}', [SupportTicketController::class, 'show']);
            Route::post('tickets/{id}/reply', [SupportMessageController::class, 'replyUser']);
        });

        // Logout
        Route::post('/logout', function (Request $request) {
            $request->user()->currentAccessToken()->delete();
            return response()->json(['message' => 'با موفقیت خارج شدید']);
        });
    });

    // Admin-only routes
    Route::middleware(['auth:sanctum', 'can:isAdmin'])->group(function () {

        // Wallet withdraw review
        Route::post('wallet/withdraw-review/{withdraw}', [WalletController::class, 'reviewWithdrawRequest']);

        // Announcements (Admin)
        Route::prefix('admin/announcements')->group(function () {
            Route::post('/', [AnnouncementController::class, 'store']);
            Route::put('{id}', [AnnouncementController::class, 'update']);
            Route::delete('{id}', [AnnouncementController::class, 'destroy']);
        });

        // Support (Admin)
        Route::prefix('admin/support')->group(function () {
            Route::get('tickets', [SupportTicketController::class, 'all']);
            Route::post('tickets/{id}/reply', [SupportMessageController::class, 'replyAdmin']);
        });

    });

    // خدمات (سرویس‌ها)
    Route::get('services', [ServiceController::class,'index']);
    Route::get('services/{id}', [ServiceController::class,'show']);

    Route::middleware(['auth:sanctum','can:isAdmin'])->group(function(){
        Route::post('services', [ServiceController::class,'store']);
        Route::put('services/{id}', [ServiceController::class,'update']);
        Route::delete('services/{id}', [ServiceController::class,'destroy']);
    });

    // سرویس‌دهندگان
    Route::middleware('auth:sanctum')->group(function(){
        Route::get('providers/{provider}/services', [ProviderServiceController::class,'index']);
        Route::post('providers/{provider}/services', [ProviderServiceController::class,'store']);
        Route::delete('providers/{provider}/services/{service}', [ProviderServiceController::class,'destroy']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        // Conversation endpoints
        Route::get('conversations/{conversation}/messages', [ChatController::class, 'messages']);
        Route::post('chat/send', [ChatController::class, 'send']);
        Route::post('conversations/{conversation}/read', [ChatController::class, 'markRead']);
        Route::post('conversations/{conversation}/block', [ChatController::class, 'block']);
        Route::post('conversations/{conversation}/unblock', [ChatController::class, 'unblock']);
        Route::post('chat/conversations', [ChatController::class, 'createConversation']);
    });

});
