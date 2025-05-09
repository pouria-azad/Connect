<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/v1/user/register/send-otp",
     *     summary="ارسال کد تایید",
     *     description="ارسال کد OTP به شماره موبایل",
     *     operationId="sendOtp",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone"},
     *             @OA\Property(property="phone", type="string", example="09123456789")
     *         )
     *     ),
     *     @OA\Response(
     *     response=429,
     *     description="محدودیت ارسال: لطفاً کمی صبر کرده و دوباره تلاش کنید"
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP ارسال شد",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="code", type="integer", example=1234)
     *         )
     *     ),
     *     @OA\Response(response=422, description="خطای اعتبارسنجی")
     * )
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $request->validate(['phone' => 'required|ir_mobile|unique:users,phone']);

        try {
            $otpKey = 'otp_' . $request->phone;
            $throttleKey = 'otp_send_throttle_' . $request->phone;

            // بررسی محدودیت زمانی برای ارسال
            if (Cache::has($throttleKey)) {
                return response()->json(['message' => 'لطفاً کمی صبر کرده و سپس تلاش کنید'], 429);
            }

            $otp = rand(1000, 9999);
            Cache::put($otpKey, $otp, now()->addMinutes(2));
            Cache::put($throttleKey, true, now()->addSeconds(60));

            // ارسال پیامک (در حالت واقعی)
            // SmsService::send($request->phone, "کد تایید شما: $otp");

            return response()->json(['message' => 'کد تایید ارسال شد', 'code' => $otp]);
        } catch (\Throwable $e) {
            Log::error('خطا در ارسال OTP', ['error' => $e->getMessage(), 'phone' => $request->phone ?? null]);
            return response()->json(['message' => 'ارسال کد با خطا مواجه شد'], 500);
        }
    }


    /**
     * @OA\Post(
     *     path="/api/v1/user/register/verify-otp",
     *     summary="تایید کد OTP",
     *     description="بررسی صحت کد ارسال‌شده",
     *     operationId="verifyOtp",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone", "otp"},
     *             @OA\Property(property="phone", type="string", example="09123456789"),
     *             @OA\Property(property="otp", type="integer", example=1234)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="شماره تایید شد",
     *         @OA\JsonContent(@OA\Property(property="message", type="string"))
     *     ),
     *     @OA\Response(response=400, description="کد نادرست یا منقضی‌شده")
     * )
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|ir_mobile|unique:users,phone',
            'otp' => 'required|numeric'
        ]);

        try {
            $cachedOtp = Cache::get('otp_' . $request->phone);

            if (!$cachedOtp) {
                Log::warning('OTP پیدا نشد', ['phone' => $request->phone]);
                return response()->json(['message' => 'کد منقضی شده یا یافت نشد'], 400);
            }

            if ($cachedOtp != $request->otp) {
                Log::notice('OTP اشتباه وارد شد', ['phone' => $request->phone, 'input_otp' => $request->otp]);
                return response()->json(['message' => 'کد نادرست است'], 400);
            }

            Cache::put('verified_' . $request->phone, true, now()->addMinutes(10));

            return response()->json(['message' => 'شماره تایید شد']);
        } catch (\Throwable $e) {
            Log::error('خطا در تایید OTP', ['error' => $e->getMessage(), 'phone' => $request->phone ?? null]);
            return response()->json(['message' => 'خطا در تایید کد'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user/register/resend-otp",
     *     summary="ارسال مجدد کد OTP",
     *     description="ارسال مجدد همان کد OTP در صورتی که قبلاً ارسال شده باشد",
     *     operationId="resendOtp",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone"},
     *             @OA\Property(property="phone", type="string", example="09123456789")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="کد تایید مجدداً ارسال شد",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="code", type="integer", example=1234)
     *         )
     *     ),
     *     @OA\Response(response=400, description="درخواست اولیه ارسال نشده یا کد منقضی شده"),
     *     @OA\Response(response=429, description="تعداد درخواست زیاد است")
     * )
     */
    public function resendOtp(Request $request): JsonResponse
    {
        $request->validate(['phone' => 'required|ir_mobile']);

        try {
            $otpKey = 'otp_' . $request->phone;
            $throttleKey = 'otp_resend_throttle_' . $request->phone;

            // بررسی وجود OTP قبلی
            $otp = Cache::get($otpKey);
            if (!$otp) {
                return response()->json(['message' => 'کدی برای ارسال مجدد یافت نشد. لطفاً ابتدا درخواست اولیه ارسال کد را انجام دهید'], 400);
            }

            // بررسی محدودیت زمانی برای ارسال مجدد
            if (Cache::has($throttleKey)) {
                return response()->json(['message' => 'لطفاً کمی صبر کرده و سپس تلاش کنید'], 429);
            }

            // تمدید اعتبار OTP و ست کردن throttle
            Cache::put($otpKey, $otp, now()->addMinutes(2));
            Cache::put($throttleKey, true, now()->addSeconds(60));

            // ارسال مجدد (ارسال واقعی باید با سرویس پیامک انجام شود)
            // SmsService::send($request->phone, "کد تایید شما: $otp");

            return response()->json(['message' => 'کد تایید مجدداً ارسال شد', 'code' => $otp]);

        } catch (\Throwable $e) {
            Log::error('خطا در ارسال مجدد OTP', ['error' => $e->getMessage(), 'phone' => $request->phone ?? null]);
            return response()->json(['message' => 'ارسال مجدد کد با خطا مواجه شد'], 500);
        }
    }



    /**
     * @OA\Post(
     *     path="/api/v1/user/register/complete-profile",
     *     summary="تکمیل اطلاعات کاربر",
     *     description="پس از تایید شماره، ثبت اطلاعات کامل انجام می‌شود",
     *     operationId="completeProfile",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone", "username", "national_id", "full_name", "password"},
     *             @OA\Property(property="phone", type="string", example="09123456789"),
     *             @OA\Property(property="username", type="string", example="myuser"),
     *             @OA\Property(property="national_id", type="string", example="1234567890"),
     *             @OA\Property(property="full_name", type="string", example="علی محمدی"),
     *             @OA\Property(property="password", type="string", example="mysecurepass"),
     *             @OA\Property(property="referral_code", type="string", example="ABCD1234", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="ثبت‌نام کامل شد",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="user", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="شماره تایید نشده"),
     *     @OA\Response(response=422, description="خطای اعتبارسنجی")
     * )
     */
    public function completeProfile(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|ir_mobile|unique:users,phone',
            'username' => 'required|unique:users,username',
            'national_id' => 'required|ir_national_id|unique:users,national_id',
            'referral_code' => 'nullable|exists:users,referral_code',
            'full_name' => 'required',
            'password' => 'required|min:6',
        ]);

        try {
            if (!Cache::get('verified_' . $request->phone)) {
                Log::warning('تلاش برای ثبت‌نام بدون تایید شماره', ['phone' => $request->phone]);
                return response()->json(['message' => 'شماره تایید نشده'], 400);
            }


            $referrer = null;
            if ($request->filled('referral_code')) {
                $referrer = User::where('referral_code', $request->referral_code)->first();
            }

            $user = User::create([
                'full_name' => $request->full_name,
                'phone' => $request->phone,
                'username' => $request->username,
                'national_id' => $request->national_id,
                'password' => bcrypt($request->password),
                'referred_by' => $referrer?->id,
            ]);

            Cache::forget('verified_' . $request->phone);

            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json(['message' => 'ثبت‌نام کامل شد', 'user' => new UserResource($user), 'token' => $token]);
        } catch (ValidationException $e) {
            Log::warning('خطای اعتبارسنجی پروفایل', ['errors' => $e->errors()]);
            return response()->json(['message' => 'خطای اعتبارسنجی', 'errors' => $e->errors()], 422);
        } catch (\Throwable $e) {
            Log::error('خطا در تکمیل پروفایل', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'خطا در ثبت‌نام'], 500);
        }
    }

}
