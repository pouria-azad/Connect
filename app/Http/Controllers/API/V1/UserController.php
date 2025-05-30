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
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => $user,
                'wallet' => $user->wallet,
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
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        $user->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'پروفایل با موفقیت بروزرسانی شد',
            'data' => [
                'user' => $user
            ]
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
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'رمز عبور فعلی اشتباه است'
            ], 400);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'message' => 'رمز عبور با موفقیت تغییر کرد'
        ]);
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
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }
        
        return response()->json([
            'total_referrals' => $user->referrals()->count(),
            'total_earnings' => $user->referralTransactions()->sum('amount'),
            'referral_code' => $user->referral_code
        ]);
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
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }
        
        $transactions = $user->referralTransactions()
            ->latest()
            ->get(['id', 'amount', 'type', 'created_at']);
        return response()->json([
            'data' => $transactions
        ]);
    }
} 