<?php

use App\Http\Controllers\API\V1\AnnouncementController;
use App\Http\Controllers\API\V1\ChatController;
use App\Http\Controllers\API\V1\ProviderServiceController;
use App\Http\Controllers\API\V1\RegisterController;
use App\Http\Controllers\API\V1\ServiceController;
use App\Http\Controllers\API\V1\SupportMessageController;
use App\Http\Controllers\API\V1\SupportTicketController;
use App\Http\Controllers\API\V1\WalletController;
use App\Http\Controllers\API\V1\AuthController;
use App\Http\Controllers\API\V1\UserController;
use App\Http\Controllers\API\V1\ReviewController;
use App\Http\Controllers\API\V1\AdvertisementController;
use App\Http\Controllers\API\V1\SubscriptionPlanController;
use App\Http\Controllers\API\V1\SubscriptionController;
use App\Http\Controllers\V1\ServiceRequestController;
use App\Http\Controllers\V1\UserBlockController;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\ProviderController;
use App\Http\Controllers\API\V1\NotificationController;

Route::prefix('v1/admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    // Subscription plans
    Route::get('subscription-plans', [SubscriptionPlanController::class, 'index']);
    Route::post('subscription-plans', [SubscriptionPlanController::class, 'store']);
    Route::get('subscription-plans/{subscriptionPlan}', [SubscriptionPlanController::class, 'show']);
    Route::put('subscription-plans/{subscriptionPlan}', [SubscriptionPlanController::class, 'update']);
    Route::delete('subscription-plans/{subscriptionPlan}', [SubscriptionPlanController::class, 'destroy']);
    Route::get('subscription-plans/{subscriptionPlan}/statistics', [SubscriptionPlanController::class, 'statistics']);
    // Support tickets (admin)
    Route::get('support/tickets', [SupportTicketController::class, 'adminIndex']);
    Route::post('support/tickets/{ticket}/reply', [SupportMessageController::class, 'adminReply']);
    // Wallet withdraw review
    Route::get('wallet/withdraw-requests', [WalletController::class, 'withdrawRequests']);
    Route::post('wallet/withdraw-requests/{withdrawRequest}/review', [WalletController::class, 'reviewWithdrawRequest']);
    // Services (admin)
    Route::post('services', [ServiceController::class, 'store']);
    Route::put('services/{id}', [ServiceController::class, 'update']);
    Route::delete('services/{id}', [ServiceController::class, 'destroy']);
    // Provider services (admin)
    Route::get('providers/{provider}/services', [ProviderServiceController::class, 'index']);
    Route::post('providers/{provider}/services', [ProviderServiceController::class, 'store']);
    Route::delete('providers/{provider}/services/{service}', [ProviderServiceController::class, 'destroy']);
    Route::delete('provider-services/{id}', [ProviderServiceController::class, 'destroyByAdmin']);
    // Announcements (admin)
    Route::post('announcements', [AnnouncementController::class, 'store']);
    Route::put('announcements/{id}', [AnnouncementController::class, 'update']);
    Route::delete('announcements/{id}', [AnnouncementController::class, 'destroy']);
});

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('send-otp', [AuthController::class, 'sendOtp']);
        Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
        Route::post('register', [AuthController::class, 'register']);
        Route::post('register-senfi-provider', [AuthController::class, 'registerSenfiProvider']);
        Route::post('register-canctyar-provider', [AuthController::class, 'registerCanctyarProvider']);
        Route::post('register-provider', [AuthController::class, 'registerProvider']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    });
    // Public review routes
    Route::get('providers/{providerId}/reviews', [ReviewController::class, 'providerReviews']);
    // Advertisement
    Route::get('advertisements', [AdvertisementController::class, 'index']);
    Route::post('advertisements/{advertisement}/click', [AdvertisementController::class, 'recordClick']);
    Route::post('advertisements/{advertisement}/display', [AdvertisementController::class, 'recordDisplay']);
    // Subscription plans
    Route::get('subscription-plans', [SubscriptionPlanController::class, 'index']);
    Route::get('subscription-plans/{subscriptionPlan}', [SubscriptionPlanController::class, 'show']);
    // Services
    Route::get('services', [ServiceController::class, 'index']);
    Route::get('services/{id}', [ServiceController::class, 'show']);
    // User profile
    Route::get('user/profile', [UserController::class, 'profile']);
    Route::put('user/change-password', [UserController::class, 'changePassword']);
    Route::get('user/referral-history', [UserController::class, 'referralHistory']);
    // Authenticated user routes
    Route::middleware('auth:sanctum')->group(function () {
        // Wallet
        Route::post('wallet/deposit', [WalletController::class, 'deposit']);
        Route::get('wallet/transactions', [WalletController::class, 'transactions']);
        Route::post('wallet/transfer', [WalletController::class, 'transfer']);
        Route::post('wallet/withdraw', [WalletController::class, 'requestWithdraw']);
        Route::get('wallet/balance', [WalletController::class, 'balance']);
        Route::post('wallet/use-gift-card', [WalletController::class, 'useGiftCard']);
        // Support (User)
        Route::post('support/tickets', [SupportTicketController::class, 'store']);
        Route::get('support/tickets', [SupportTicketController::class, 'index']);
        Route::get('support/tickets/{id}', [SupportTicketController::class, 'show']);
        Route::post('support/tickets/{id}/reply', [SupportMessageController::class, 'replyUser']);
        // Review
        Route::post('reviews', [ReviewController::class, 'store']);
        Route::put('reviews/{review}', [ReviewController::class, 'update']);
        Route::delete('reviews/{review}', [ReviewController::class, 'destroy']);
        Route::get('reviews/my-reviews', [ReviewController::class, 'customerReviews']);
        // User profile
        Route::get('user/profile', [UserController::class, 'profile']);
        Route::put('user/profile', [UserController::class, 'updateProfile']);
        Route::put('user/change-password', [UserController::class, 'changePassword']);
        Route::get('user/referral-stats', [UserController::class, 'referralStats']);
        Route::get('user/referral-history', [UserController::class, 'referralHistory']);
        // Provider services (user)
        Route::get('providers/{provider}/services', [ProviderServiceController::class, 'index']);
        Route::post('providers/{provider}/services', [ProviderServiceController::class, 'store']);
        Route::delete('providers/{provider}/services/{service}', [ProviderServiceController::class, 'destroy']);
        // Services (user)
        Route::post('services', [ServiceController::class, 'store']);
        Route::put('services/{id}', [ServiceController::class, 'update']);
        Route::delete('services/{id}', [ServiceController::class, 'destroy']);
        // Logout
        Route::post('logout', function (Request $request) {
            $request->user()->currentAccessToken()->delete();
            return response()->json(['message' => 'با موفقیت خارج شدید']);
        });
        // User block
        Route::post('users/{user}/block', [UserBlockController::class, 'block']);
        Route::post('users/{user}/unblock', [UserBlockController::class, 'unblock']);
        // User Advertisements
        Route::get('my-advertisements', [AdvertisementController::class, 'userIndex']);
        Route::get('my-advertisements/{id}', [AdvertisementController::class, 'userShow']);
        Route::post('my-advertisements/{id}/renew', [AdvertisementController::class, 'renew']);
        // Invoice routes
        Route::get('invoices', [\App\Http\Controllers\API\V1\InvoiceController::class, 'index']);
        Route::get('invoices/{id}', [\App\Http\Controllers\API\V1\InvoiceController::class, 'show']);
        Route::post('invoices', [\App\Http\Controllers\API\V1\InvoiceController::class, 'store']);
        Route::post('invoices/{id}/pay', [\App\Http\Controllers\API\V1\InvoiceController::class, 'pay']);
        // مدیریت فایل‌های درخواست خدمت
        Route::get('service-requests/{serviceRequest}/files', [\App\Http\Controllers\API\V1\RequestFileController::class, 'index']);
        Route::delete('request-files/{id}', [\App\Http\Controllers\API\V1\RequestFileController::class, 'destroy']);
        Route::get('request-files/{id}/download', [\App\Http\Controllers\API\V1\RequestFileController::class, 'download']);
    });
    // Subscription routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('subscriptions/subscribe', [SubscriptionController::class, 'subscribe']);
        Route::post('subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel']);
        Route::get('subscriptions/current', [SubscriptionController::class, 'current']);
        Route::get('subscriptions/history', [SubscriptionController::class, 'history']);
        Route::get('subscriptions', [SubscriptionController::class, 'index']);
        Route::get('subscriptions/{subscription}', [SubscriptionController::class, 'show']);
    });
    // Service Requests
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::apiResource('service-requests', ServiceRequestController::class);
        Route::post('service-requests/{serviceRequest}/accept', [ServiceRequestController::class, 'accept'])->name('service-requests.accept');
        Route::post('service-requests/{serviceRequest}/reject', [ServiceRequestController::class, 'reject'])->name('service-requests.reject');
        Route::post('service-requests/{serviceRequest}/complete', [ServiceRequestController::class, 'complete'])->name('service-requests.complete');
        Route::post('service-requests/{serviceRequest}/cancel', [ServiceRequestController::class, 'cancel'])->name('service-requests.cancel');
    });
    // Chat routes
    Route::get('conversations/{conversation}/messages', [ChatController::class, 'messages']);
    Route::post('chat/send', [ChatController::class, 'send']);
    Route::post('conversations/{conversation}/read', [ChatController::class, 'markRead']);
    Route::post('conversations/{conversation}/block', [ChatController::class, 'block']);
    Route::post('conversations/{conversation}/unblock', [ChatController::class, 'unblock']);
    // Review routes
    Route::get('reviews', [ReviewController::class, 'index']);
    // Public announcement route
    Route::middleware('auth:sanctum')->get('announcements', [AnnouncementController::class, 'index']);
    // Shop search route
    Route::get('shop/search', [\App\Http\Controllers\API\V1\ShopSearchController::class, 'search']);
    // Suggested provider route
    Route::get('providers/suggested', [\App\Http\Controllers\API\V1\SuggestedProviderController::class, 'index']);
    // Provider routes
    Route::middleware(['auth:sanctum'])
        ->prefix('provider')->group(function () {
        // Common provider routes
        Route::get('profile', [ProviderController::class, 'profile']);
        Route::put('profile', [UserController::class, 'updateProviderProfile']);
        Route::get('statistics', [UserController::class, 'providerStatistics']);
        Route::get('reviews', [ReviewController::class, 'providerReviews']);
        // Senfi provider routes
        Route::middleware(['provider.type:senfi'])->group(function () {
            Route::get('business-hours', [ProviderController::class, 'getBusinessHours']);
            Route::put('business-hours', [ProviderController::class, 'updateBusinessHours']);
            Route::get('payment-methods', [ProviderController::class, 'getPaymentMethods']);
            Route::put('payment-methods', [ProviderController::class, 'updatePaymentMethods']);
        });
        // Canctyar provider routes
        Route::middleware(['provider.type:canctyar'])->group(function () {
            Route::get('service-areas', [ProviderController::class, 'getServiceAreas']);
            Route::put('service-areas', [ProviderController::class, 'updateServiceAreas']);
            Route::get('availability', [ProviderController::class, 'getAvailability']);
            Route::put('availability', [ProviderController::class, 'updateAvailability']);
            Route::put('travel-settings', [ProviderController::class, 'updateTravelSettings']);
        });
    });
    // Notification routes
    Route::middleware('auth:sanctum')->prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread', [NotificationController::class, 'unread']);
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
    });
});
