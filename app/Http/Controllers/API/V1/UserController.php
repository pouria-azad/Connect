<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\User\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user/profile",
     *     summary="دریافت پروفایل کاربر (User)",
     *     tags={"User (User)"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="اطلاعات پروفایل کاربر"
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function profile(): JsonResponse
    {
        $user = auth()->user()->load(['wallet', 'clubWallet']);
        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => $user,
                'wallet' => $user->wallet
            ]
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/user/profile",
     *     summary="ویرایش پروفایل کاربر (User)",
     *     tags={"User (User)"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string", format="email")
     *         )
     *     ),
     *     @OA\Response(response=200, description="پروفایل با موفقیت بروزرسانی شد"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        $request->validate([
            'full_name' => 'sometimes|required|string|max:255',
            'username' => 'sometimes|required|string|min:3|max:255|unique:users,username,'.$user->id,
            'national_code' => 'sometimes|required|string|size:10|unique:users,national_code,'.$user->id,
        ]);

        $user->update($request->only([
            'full_name',
            'username',
            'national_code'
        ]));

        return response()->json([
            'message' => 'پروفایل با موفقیت به‌روز شد'
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/user/change-password",
     *     summary="تغییر رمز عبور کاربر (User)",
     *     tags={"User (User)"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"current_password","password","password_confirmation"},
     *             @OA\Property(property="current_password", type="string"),
     *             @OA\Property(property="password", type="string", minLength=8),
     *             @OA\Property(property="password_confirmation", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="رمز عبور با موفقیت تغییر کرد"),
     *     @OA\Response(response=400, description="رمز عبور فعلی اشتباه است"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed'
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'رمز عبور فعلی اشتباه است'], 400);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json(['message' => 'رمز عبور با موفقیت تغییر کرد']);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user/referral-stats",
     *     summary="دریافت آمار ارجاع‌های کاربر (User)",
     *     tags={"User (User)"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="آمار ارجاع‌ها"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function referralStats(): JsonResponse
    {
        $user = auth()->user();
        
        $stats = [
            'total_referrals' => $user->referrals()->count(),
            'successful_referrals' => $user->referrals()->whereNotNull('mobile_verified_at')->count(),
            'total_earnings' => $user->referralTransactions()->sum('bonus_amount_per_user'),
        ];

        return response()->json($stats);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user/referral-history",
     *     summary="دریافت تاریخچه تراکنش‌های ارجاع کاربر (User)",
     *     tags={"User (User)"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="تاریخچه تراکنش‌های ارجاع"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function referralHistory(): JsonResponse
    {
        $user = auth()->user();
        
        $history = $user->referralTransactions()
            ->with(['relatedUser:id,username,full_name,mobile_verified_at'])
            ->orderBy('created_at', 'desc')
            ->paginate();

        return response()->json([
            'data' => $history->items()
        ]);
    }

    /**
     * دریافت پروفایل ارائه‌دهنده کاربر جاری
     *
     * @OA\Get(
     *     path="/api/v1/provider/profile",
     *     summary="دریافت پروفایل ارائه‌دهنده (Provider)",
     *     tags={"Provider (User)"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="اطلاعات پروفایل ارائه‌دهنده"),
     *     @OA\Response(response=404, description="پروفایل ارائه‌دهنده یافت نشد")
     * )
     */
    public function providerProfile(): JsonResponse
    {
        $user = auth()->user();
        $provider = $user->provider;
        
        if (!$provider) {
            return response()->json(['message' => 'Provider profile not found'], 404);
        }

        // Load relationships based on provider type
        if ($provider->provider_type === 'senfi') {
            $provider->load('senfi');
        } else if ($provider->provider_type === 'canctyar') {
            $provider->load('canctyar');
        }

        return response()->json([
            'user' => $user,
            'provider' => $provider
        ]);
    }

    public function updateProviderProfile(Request $request): JsonResponse
    {
        $user = auth()->user();
        $provider = $user->provider;

        if (!$provider) {
            return response()->json(['message' => 'Provider profile not found'], 404);
        }

        // Validate common provider fields
        $request->validate([
            'bio' => 'sometimes|required|string',
            'province_id' => 'sometimes|required|exists:provinces,id',
            'city_id' => 'sometimes|required|exists:cities,id',
            'can_serve_nation_wide' => 'sometimes|required|boolean',
            'expertise_areas' => 'sometimes|required|string',
            'profile_image' => 'sometimes|required|string',
        ]);

        // Update provider
        $provider->update($request->only([
            'bio',
            'province_id',
            'city_id',
            'can_serve_nation_wide',
            'expertise_areas',
            'profile_image',
        ]));

        // Update type-specific fields
        if ($provider->provider_type === 'senfi') {
            $request->validate([
                'business_license_number' => 'sometimes|required|string',
                'tax_id' => 'sometimes|required|string',
                'business_address' => 'sometimes|required|string',
                'business_phone' => 'sometimes|required|string',
                'business_hours' => 'sometimes|required|array',
                'accepted_payment_methods' => 'sometimes|required|array',
                'has_physical_store' => 'sometimes|required|boolean',
            ]);

            $provider->senfi->update($request->only([
                'business_license_number',
                'tax_id',
                'business_address',
                'business_phone',
                'business_hours',
                'accepted_payment_methods',
                'has_physical_store',
            ]));
        } else if ($provider->provider_type === 'canctyar') {
            $request->validate([
                'certification_number' => 'sometimes|required|string',
                'skills' => 'sometimes|required|array',
                'service_areas' => 'sometimes|required|array',
                'availability_hours' => 'sometimes|required|array',
                'can_travel' => 'sometimes|required|boolean',
                'travel_fee_per_km' => 'sometimes|required_if:can_travel,true|numeric|min:0',
                'minimum_service_fee' => 'sometimes|required|numeric|min:0',
            ]);

            $provider->canctyar->update($request->only([
                'certification_number',
                'skills',
                'service_areas',
                'availability_hours',
                'can_travel',
                'travel_fee_per_km',
                'minimum_service_fee',
            ]));
        }

        return response()->json([
            'message' => 'پروفایل با موفقیت به‌روز شد',
            'provider' => $provider->fresh()
        ]);
    }

    public function providerStatistics(): JsonResponse
    {
        $provider = auth()->user()->provider;

        if (!$provider) {
            return response()->json(['message' => 'Provider profile not found'], 404);
        }

        return response()->json([
            'statistics' => [
                'average_rating' => $provider->average_rating,
                'successful_orders_count' => $provider->successful_orders_count,
                'total_reviews' => $provider->reviews()->count(),
                'total_earnings' => $provider->wallet->total_earnings ?? 0,
                'last_activity' => $provider->last_activity_at,
            ]
        ]);
    }
} 