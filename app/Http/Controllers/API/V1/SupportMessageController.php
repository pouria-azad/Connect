<?php
namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\SupportMessage\storeSupportMessageRequest;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;

class SupportMessageController extends Controller
{
    use AuthorizesRequests;
    /**
     * @OA\Post(
     *     path="/api/v1/support/tickets/{id}/reply",
     *     summary="ارسال پاسخ کاربر به تیکت",
     *     tags={"Support Messages (User)"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="شناسه تیکت",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="لطفاً مشکل رو بررسی کنید")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="پیام با موفقیت ارسال شد",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="پیام ارسال شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="دسترسی غیرمجاز",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="تیکت یافت نشد",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Not Found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="نیاز به احراز هویت",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="خطای اعتبارسنجی",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The message field is required.")
     *         )
     *     )
     * )
     */
    public function replyUser(storeSupportMessageRequest $request, $id)
    {
        $ticket = SupportTicket::findOrFail($id);
        $this->authorize('view', $ticket);

        $ticket->messages()->create([
            'message' => $request->validated()['message'],
            'user_id' => $request->user()->id,
            'is_admin' => false,
        ]);

        $ticket->update(['status' => 'open']);

        return response()->json(['message' => 'پیام ارسال شد']);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/support/tickets/{id}/reply",
     *     summary="ارسال پاسخ ادمین به تیکت",
     *     tags={"Support Messages (Admin)"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="شناسه تیکت",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="مشکل بررسی شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="پاسخ با موفقیت ثبت شد",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="پاسخ ثبت شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="دسترسی غیرمجاز",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="تیکت یافت نشد",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Not Found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="نیاز به احراز هویت",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="خطای اعتبارسنجی",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The message field is required.")
     *         )
     *     )
     * )
     */
    public function replyAdmin(storeSupportMessageRequest $request, $id)
    {
        $user = $request->user();

        if (! Gate::allows('isAdmin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }


        $ticket = SupportTicket::findOrFail($id);

        $ticket->messages()->create([
            'message' => $request->validated()['message'],
            'user_id' => null,
            'is_admin' => true,
        ]);

        $ticket->update(['status' => 'answered']);

        return response()->json(['message' => 'پاسخ ثبت شد']);
    }
}
