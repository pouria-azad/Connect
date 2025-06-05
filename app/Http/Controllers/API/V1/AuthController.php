<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Auth\SendOtpRequest;
use App\Http\Requests\V1\Auth\VerifyOtpRequest;
use App\Http\Requests\V1\Auth\RegisterRequest;
use App\Http\Requests\V1\Auth\RegisterSenfiProviderRequest;
use App\Http\Requests\V1\Auth\RegisterCanctyarProviderRequest;
use App\Models\OtpCode;
use App\Models\User;
use App\Models\Wallet;
use App\Models\GiftCard;
use App\Models\WalletTransaction;
use App\Models\ReferralTransaction;
use App\Models\Provider;
use App\Models\ProviderSenfi;
use App\Models\ProviderCanctyar;
use App\Models\ClubWallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Services\SmsIrService;
use App\Services\ProviderRegistrationService;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="API endpoints for user authentication"
 * )
 */
class AuthController extends Controller
{
    private NotificationService $notificationService;
    private SmsIrService $smsIrService;
    private ProviderRegistrationService $providerRegistrationService;

    public function __construct(
        NotificationService $notificationService,
        SmsIrService $smsIrService,
        ProviderRegistrationService $providerRegistrationService
    ) {
        $this->notificationService = $notificationService;
        $this->smsIrService = $smsIrService;
        $this->providerRegistrationService = $providerRegistrationService;
    }

    /**
     * Send OTP code to user's mobile number
     *
     * @OA\Post(
     *     path="/api/v1/auth/send-otp",
     *     summary="ارسال کد تایید (User)",
     *     tags={"Auth (User)"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="mobile_number", type="string", example="9123456789")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP code sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="کد تأیید ارسال شد"),
     *             @OA\Property(property="code", type="string", example="123456")
     *         )
     *     )
     * )
     */
    public function sendOtp(SendOtpRequest $request): JsonResponse
    {
        $mobile_number = ltrim($request->mobile_number, '0');

        // Generate 6-digit OTP code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store OTP in database
        OtpCode::updateOrCreate(
            ['mobile_number' => $mobile_number],
            [
                'code' => $code,
                'expires_at' => now()->addMinutes(2),
                'is_used' => false
            ]
        );

        // تعیین نوع پیامک (ثبت‌نام یا ورود)
        $type = User::where('mobile_number', $mobile_number)->exists() ? 'login' : 'register';

        // ارسال پیامک
        $smsResult = $this->smsIrService->sendVerificationCode($mobile_number, $code, $type);

        if (!$smsResult) {
            return response()->json([
                'message' => 'خطا در ارسال پیامک. لطفا مجددا تلاش کنید.'
            ], 500);
        }

        // برای توسعه، کد را برمی‌گردانیم (در تولید حذف کن)
        return response()->json([
            'message' => 'کد تأیید ارسال شد',
            'code' => $code, // Remove this in production
        ]);
    }

    /**
     * Verify OTP code
     *
     * @OA\Post(
     *     path="/api/v1/auth/verify-otp",
     *     summary="تایید کد ارسالی (User)",
     *     tags={"Auth (User)"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="mobile_number", type="string", example="9123456789"),
     *             @OA\Property(property="code", type="string", example="123456")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP code verified successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="کد تأیید معتبر است"),
     *             @OA\Property(property="is_new_user", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid OTP code",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="کد تأیید نامعتبر است")
     *         )
     *     )
     * )
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $mobile_number = ltrim($request->mobile_number, '0');
        $code = $request->code;

        $otp = OtpCode::where('mobile_number', $mobile_number)
            ->where('code', $code)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otp) {
            return response()->json(['message' => 'کد تأیید نامعتبر است'], 400);
        }

        // Mark OTP as used
        $otp->update(['is_used' => true]);

        // Store verification in cache for 5 minutes
        Cache::put('verified_' . $mobile_number, true, now()->addMinutes(5));

        // Check if user exists
        $user = User::where('mobile_number', $mobile_number)->first();

        if ($user) {
            // Generate token for existing user
            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'message' => 'ورود موفقیت‌آمیز',
                'token' => $token,
                'user' => $user,
            ]);
        }

        return response()->json([
            'message' => 'کد تأیید معتبر است',
            'is_new_user' => true,
        ]);
    }

    /**
     * Register new user
     *
     * @OA\Post(
     *     path="/api/v1/auth/register",
     *     summary="ثبت نام کاربر جدید (User)",
     *     tags={"Auth (User)"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="full_name", type="string", example="John Doe"),
     *             @OA\Property(property="mobile_number", type="string", example="9123456789"),
     *             @OA\Property(property="username", type="string", example="johndoe"),
     *             @OA\Property(property="national_code", type="string", example="1234567890"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", example="password123"),
     *             @OA\Property(property="referral_code", type="string", example="ABC123", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="ثبت‌نام موفقیت‌آمیز"),
     *             @OA\Property(property="token", type="string", example="1|abcdef1234567890"),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="full_name", type="string", example="John Doe"),
     *                 @OA\Property(property="mobile_number", type="string", example="9123456789"),
     *                 @OA\Property(property="username", type="string", example="johndoe"),
     *                 @OA\Property(property="referral_code", type="string", example="ABC123"),
     *                 @OA\Property(property="is_admin", type="boolean", example=false),
     *                 @OA\Property(property="mobile_verified_at", type="string", format="date-time", example="2025-05-09T12:00:00Z"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-05-09T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-05-09T12:00:00Z")
     *             ),
     *             @OA\Property(
     *                 property="gift_card",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="ABC123"),
     *                 @OA\Property(property="current_balance", type="integer", example=5000),
     *                 @OA\Property(property="expires_at", type="string", format="date-time", example="2025-11-09T12:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="شماره تأیید نشده است")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="خطا در ثبت‌نام")
     *         )
     *     )
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $mobile_number = ltrim($request->mobile_number, '0');
        if (!Cache::get('verified_' . $mobile_number)) {
            return response()->json(['message' => 'شماره تأیید نشده است'], 400);
        }

        try {
            DB::beginTransaction();

            // Find referrer if referral code provided
            $referrer = $request->filled('referral_code')
                ? User::where('referral_code', $request->referral_code)->first()
                : null;

            // Create user
            $user = User::create([
                'full_name' => $request->full_name,
                'display_name' => $request->full_name,
                'mobile_number' => $mobile_number,
                'username' => $request->username,
                'national_code' => $request->national_code,
                'password' => Hash::make($request->password),
                'referral_code' => $this->generateUniqueReferralCode(),
                'referred_by_user_id' => $referrer?->id,
                'user_type' => 'regular',
            ]);

            // Create wallet
            $wallet = Wallet::create([
                'user_id' => $user->id,
                'balance' => 0,
            ]);

            // Create gift card
            $giftCard = GiftCard::create([
                'code' => $this->generateUniqueGiftCardCode(),
                'initial_balance' => 0,
                'amount' => 0,
                'current_balance' => 0,
                'expires_at' => now()->addMonths(6),
                'is_active' => true,
                'assigned_to_user_id' => $user->id,
                'source_type' => 'admin_issued',
            ]);

            // Handle referral bonus if referrer exists
            if ($referrer) {
                $bonusAmount = 5000; // Configurable amount

                // Add bonus to new user's gift card
                $giftCard->current_balance += $bonusAmount;
                $giftCard->save();

                // Add bonus to referrer's gift card
                $referrerGiftCard = GiftCard::where('assigned_to_user_id', $referrer->id)->first();
                if (!$referrerGiftCard) {
                    $referrerGiftCard = GiftCard::create([
                        'code' => $this->generateUniqueGiftCardCode(),
                        'initial_balance' => 0,
                        'current_balance' => 0,
                        'expires_at' => now()->addMonths(6),
                        'is_active' => true,
                        'assigned_to_user_id' => $referrer->id,
                        'source_type' => 'admin_issued',
                    ]);
                }
                $referrerGiftCard->current_balance += $bonusAmount;
                $referrerGiftCard->save();

                // Create wallet transactions
                $newUserWallet = $user->wallet;
                if (!$newUserWallet) {
                    $newUserWallet = Wallet::create([
                        'user_id' => $user->id,
                        'balance' => 0,
                    ]);
                }
                $newUserTransaction = WalletTransaction::create([
                    'wallet_id' => $newUserWallet->id,
                    'user_id' => $user->id,
                    'amount' => $bonusAmount,
                    'type' => 'deposit_gift_card',
                    'description' => 'پاداش معرفی (کاربر جدید)',
                    'status' => 'completed',
                    'destination_wallet_id' => $newUserWallet->id,
                    'reference_id' => $giftCard->id,
                ]);

                $referrerWallet = $referrer->wallet;
                if (!$referrerWallet) {
                    $referrerWallet = Wallet::create([
                        'user_id' => $referrer->id,
                        'balance' => 0,
                    ]);
                }
                $referrerTransaction = WalletTransaction::create([
                    'wallet_id' => $referrerWallet->id,
                    'user_id' => $referrer->id,
                    'amount' => $bonusAmount,
                    'type' => 'deposit_gift_card',
                    'description' => 'پاداش معرفی (معرف)',
                    'status' => 'completed',
                    'destination_wallet_id' => $referrerWallet->id,
                    'reference_id' => $referrerGiftCard->id,
                ]);

                // Create referral transaction
                if ($newUserTransaction && $referrerTransaction) {
                    ReferralTransaction::create([
                        'new_user_id' => $user->id,
                        'referrer_user_id' => $referrer->id,
                        'bonus_amount_per_user' => $bonusAmount,
                        'referral_date' => now(),
                        'new_user_wallet_transaction_id' => $newUserTransaction->id,
                        'referrer_user_wallet_transaction_id' => $referrerTransaction->id,
                    ]);
                }
            }

            DB::commit();

            // Clear verification cache
            Cache::forget('verified_' . $mobile_number);

            // Generate token
            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'message' => 'ثبت‌نام موفقیت‌آمیز',
                'token' => $token,
                'user' => $user,
                'gift_card' => [
                    'code' => $giftCard->code,
                    'current_balance' => $giftCard->current_balance,
                    'expires_at' => $giftCard->expires_at,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'خطا در ثبت‌نام', 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
        }
    }

    /**
     * Generate unique referral code
     */
    private function generateUniqueReferralCode(): string
    {
        do {
            $code = Str::random(8);
        } while (User::where('referral_code', $code)->exists());

        return $code;
    }

    /**
     * Generate unique gift card code
     */
    private function generateUniqueGiftCardCode(): string
    {
        do {
            $code = Str::random(10);
        } while (GiftCard::where('code', $code)->exists());

        return $code;
    }

    /**
     * ثبت نام خدمات‌دهنده جدید
     *
     * @OA\Post(
     *     path="/api/v1/auth/register-provider",
     *     summary="ثبت نام خدمات‌دهنده جدید (Provider)",
     *     tags={"Auth (Provider)"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="full_name", type="string", example="Ali Ahmadi"),
     *             @OA\Property(property="mobile_number", type="string", example="9123456789"),
     *             @OA\Property(property="username", type="string", example="alihamadi"),
     *             @OA\Property(property="national_code", type="string", example="1234567890"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", example="password123"),
     *             @OA\Property(property="referral_code", type="string", example="ABC123", nullable=true),
     *             @OA\Property(property="bio", type="string", example="متخصص برقکار"),
     *             @OA\Property(property="shop_name", type="string", example="فروشگاه علی"),
     *             @OA\Property(property="shop_address", type="string", example="تهران، خیابان آزادی، پلاک ۱۲۳"),
     *             @OA\Property(property="shop_phone", type="string", example="02112345678"),
     *             @OA\Property(property="shop_logo", type="string", example="/uploads/shops/logo.png"),
     *             @OA\Property(property="shop_description", type="string", example="فروشگاه تخصصی ابزارآلات و تجهیزات"),
     *             @OA\Property(property="senfi_number", type="string", example="123456789"),
     *             @OA\Property(property="occupation_id", type="integer", example=1),
     *             @OA\Property(property="province_id", type="integer", example=1),
     *             @OA\Property(property="city_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Provider registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="ثبت‌نام خدمات‌دهنده موفقیت‌آمیز بود"),
     *             @OA\Property(property="token", type="string", example="1|abcdef1234567890"),
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="provider", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="شماره تأیید نشده است")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="خطا در ثبت‌نام خدمات‌دهنده")
     *         )
     *     )
     * )
     */
    public function registerProvider(RegisterRequest $request): JsonResponse
    {
        if (!Cache::get('verified_' . $request->mobile_number)) {
            return response()->json(['message' => 'شماره تأیید نشده است'], 400);
        }

        try {
            DB::beginTransaction();

            $referrer = $request->filled('referral_code')
                ? User::where('referral_code', $request->referral_code)->first()
                : null;

            $user = User::create([
                'full_name' => $request->full_name,
                'display_name' => $request->full_name,
                'mobile_number' => $request->mobile_number,
                'username' => $request->username,
                'national_code' => $request->national_code,
                'password' => Hash::make($request->password),
                'referral_code' => $this->generateUniqueReferralCode(),
                'referred_by_user_id' => $referrer?->id,
                'user_type' => 'provider',
            ]);

            $wallet = Wallet::create([
                'user_id' => $user->id,
                'balance' => 0,
            ]);

            $giftCard = GiftCard::create([
                'code' => $this->generateUniqueGiftCardCode(),
                'initial_balance' => 0,
                'amount' => 0,
                'current_balance' => 0,
                'expires_at' => now()->addMonths(6),
                'is_active' => true,
                'assigned_to_user_id' => $user->id,
                'source_type' => 'admin_issued',
            ]);

            // ساخت Provider
            $provider = \App\Models\Provider::create([
                'user_id' => $user->id,
                'bio' => $request->input('bio'),
                'shop_name' => $request->input('shop_name'),
                'shop_address' => $request->input('shop_address'),
                'shop_phone' => $request->input('shop_phone'),
                'shop_logo' => $request->input('shop_logo'),
                'shop_description' => $request->input('shop_description'),
                'senfi_number' => $request->input('senfi_number'),
                'occupation_id' => $request->input('occupation_id'),
                'province_id' => $request->input('province_id'),
                'city_id' => $request->input('city_id'),
            ]);

            // پاداش معرف مثل متد register
            if ($referrer) {
                $bonusAmount = 5000;
                $giftCard->current_balance += $bonusAmount;
                $giftCard->save();
                $referrerGiftCard = GiftCard::where('assigned_to_user_id', $referrer->id)->first();
                if (!$referrerGiftCard) {
                    $referrerGiftCard = GiftCard::create([
                        'code' => $this->generateUniqueGiftCardCode(),
                        'initial_balance' => 0,
                        'current_balance' => 0,
                        'expires_at' => now()->addMonths(6),
                        'is_active' => true,
                        'assigned_to_user_id' => $referrer->id,
                        'source_type' => 'admin_issued',
                    ]);
                }
                $referrerGiftCard->current_balance += $bonusAmount;
                $referrerGiftCard->save();
                $newUserWallet = $user->wallet;
                if (!$newUserWallet) {
                    $newUserWallet = Wallet::create([
                        'user_id' => $user->id,
                        'balance' => 0,
                    ]);
                }
                $newUserTransaction = WalletTransaction::create([
                    'wallet_id' => $newUserWallet->id,
                    'user_id' => $user->id,
                    'amount' => $bonusAmount,
                    'type' => 'deposit_gift_card',
                    'description' => 'پاداش معرفی (خدمات‌دهنده جدید)',
                    'status' => 'completed',
                    'destination_wallet_id' => $newUserWallet->id,
                    'reference_id' => $giftCard->id,
                ]);
                $referrerWallet = $referrer->wallet;
                if (!$referrerWallet) {
                    $referrerWallet = Wallet::create([
                        'user_id' => $referrer->id,
                        'balance' => 0,
                    ]);
                }
                $referrerTransaction = WalletTransaction::create([
                    'wallet_id' => $referrerWallet->id,
                    'user_id' => $referrer->id,
                    'amount' => $bonusAmount,
                    'type' => 'deposit_gift_card',
                    'description' => 'پاداش معرفی (معرف)',
                    'status' => 'completed',
                    'destination_wallet_id' => $referrerWallet->id,
                    'reference_id' => $referrerGiftCard->id,
                ]);
                if ($newUserTransaction && $referrerTransaction) {
                    ReferralTransaction::create([
                        'new_user_id' => $user->id,
                        'referrer_user_id' => $referrer->id,
                        'bonus_amount_per_user' => $bonusAmount,
                        'referral_date' => now(),
                        'new_user_wallet_transaction_id' => $newUserTransaction->id,
                        'referrer_user_wallet_transaction_id' => $referrerTransaction->id,
                    ]);
                }
            }

            DB::commit();
            Cache::forget('verified_' . $request->mobile_number);
            $token = $user->createToken('api-token')->plainTextToken;
            return response()->json([
                'message' => 'ثبت‌نام خدمات‌دهنده موفقیت‌آمیز بود',
                'token' => $token,
                'user' => $user,
                'provider' => $provider,
                'gift_card' => [
                    'code' => $giftCard->code,
                    'current_balance' => $giftCard->current_balance,
                    'expires_at' => $giftCard->expires_at,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'خطا در ثبت‌نام خدمات‌دهنده', 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
        }
    }

    public function registerSenfiProvider(RegisterSenfiProviderRequest $request): JsonResponse
    {
        try {
            $result = $this->providerRegistrationService->register(
                $request->validated(),
                'senfi'
            );

            return response()->json($result, 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'خطا در ثبت‌نام خدمات‌دهنده',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function registerCanctyarProvider(RegisterCanctyarProviderRequest $request): JsonResponse
    {
        try {
            $result = $this->providerRegistrationService->register(
                $request->validated(),
                'canctyar'
            );

            return response()->json($result, 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'خطا در ثبت‌نام خدمات‌دهنده',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'mobile_number' => 'required|string',
            'password' => 'required|string'
        ]);

        $user = User::where('mobile_number', $request->mobile_number)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'mobile_number' => ['اطلاعات ورود نادرست است']
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        // Load relationships based on user type
        if ($user->isProvider()) {
            $user->load(['wallet', 'clubWallet']);
            if ($user->provider) {
                if ($user->provider_type === 'senfi') {
                    $user->load('provider.senfi');
                } else if ($user->provider_type === 'canctyar') {
                    $user->load('provider.canctyar');
                }
            }
        } else {
            $user->load(['wallet', 'clubWallet']);
        }

        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'با موفقیت خارج شدید'
        ]);
    }
}
