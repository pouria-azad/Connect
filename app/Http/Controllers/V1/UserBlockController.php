<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\UserBlock;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserBlockController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/users/{user}/block",
     *     summary="بلاک کردن یک کاربر به صورت کلی (global)",
     *     tags={"UserBlock"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="user", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=false, @OA\JsonContent(
     *         @OA\Property(property="reason", type="string", example="رفتار نامناسب")
     *     )),
     *     @OA\Response(response=200, description="کاربر با موفقیت بلاک شد"),
     *     @OA\Response(response=422, description="نمی‌توانید خودتان را بلاک کنید")
     * )
     */
    public function block(Request $request, $blocked_user_id)
    {
        $blocker = Auth::user();
        $blocked = User::findOrFail($blocked_user_id);

        if ($blocker->id === $blocked->id) {
            return response()->json(['message' => 'You cannot block yourself.'], 422);
        }

        $block = UserBlock::updateOrCreate(
            [
                'blocker_user_id' => $blocker->id,
                'blocked_user_id' => $blocked->id,
            ],
            [
                'is_active' => true,
                'reason' => $request->input('reason'),
                'blocked_at' => now(),
            ]
        );

        return response()->json(['message' => 'User blocked successfully.', 'block' => $block]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/users/{user}/unblock",
     *     summary="آنبلاک کردن یک کاربر به صورت کلی (global)",
     *     tags={"UserBlock"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="user", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="کاربر با موفقیت آنبلاک شد"),
     *     @OA\Response(response=404, description="بلاک فعالی یافت نشد")
     * )
     */
    public function unblock(Request $request, $blocked_user_id)
    {
        $blocker = Auth::user();
        $blocked = User::findOrFail($blocked_user_id);

        $block = UserBlock::where('blocker_user_id', $blocker->id)
            ->where('blocked_user_id', $blocked->id)
            ->where('is_active', true)
            ->first();

        if ($block) {
            $block->is_active = false;
            $block->save();
            return response()->json(['message' => 'User unblocked successfully.']);
        }

        return response()->json(['message' => 'No active block found.'], 404);
    }
} 