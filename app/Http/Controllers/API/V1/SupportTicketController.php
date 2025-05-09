<?php
namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\SupportTicket\StoreSupportTicketRequest;
use App\Models\SupportTicket;

class SupportTicketController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/support-tickets",
     *     summary="دریافت لیست تیکت‌های کاربر",
     *     tags={"Support Tickets"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="لیست تیکت‌های کاربر",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="subject", type="string", example="مشکل پرداخت"),
     *                 @OA\Property(property="status", type="string", enum={"open", "answered", "closed"}, example="open"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-05-09T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-05-09T12:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="نیاز به احراز هویت",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        return response()->json($request->user()->supportTickets()->latest()->get());
    }

    /**
     * @OA\Post(
     *     path="/api/v1/support-tickets",
     *     summary="ایجاد تیکت جدید",
     *     tags={"Support Tickets"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="subject", type="string", example="مشکل پرداخت")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="تیکت با موفقیت ایجاد شد",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="تیکت ایجاد شد"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="subject", type="string", example="مشکل پرداخت"),
     *                 @OA\Property(property="status", type="string", example="open"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-05-09T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-05-09T12:00:00Z")
     *             )
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
     *             @OA\Property(property="message", type="string", example="The subject field is required.")
     *         )
     *     )
     * )
     */
    public function store(StoreSupportTicketRequest $request)
    {
        $ticket = $request->user()->supportTickets()->create($request->validated());
        return response()->json(['message' => 'تیکت ایجاد شد', 'data' => $ticket]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/support-tickets/{id}",
     *     summary="دریافت جزئیات تیکت",
     *     tags={"Support Tickets"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="جزئیات تیکت",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="subject", type="string", example="مشکل پرداخت"),
     *             @OA\Property(property="status", type="string", example="open"),
     *             @OA\Property(
     *                 property="messages",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="ticket_id", type="integer", example=1),
     *                     @OA\Property(property="user_id", type="integer", nullable=true, example=1),
     *                     @OA\Property(property="message", type="string", example="لطفاً بررسی کنید"),
     *                     @OA\Property(property="is_admin", type="boolean", example=false),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-05-09T12:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-05-09T12:00:00Z")
     *                 )
     *             ),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-05-09T12:00:00Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-05-09T12:00:00Z")
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
     *     )
     * )
     */
    public function show(Request $request, $id)
    {
        $ticket = SupportTicket::with('messages')->findOrFail($id);
        $this->authorize('view', $ticket);
        return response()->json($ticket);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/support-tickets/all",
     *     summary="دریافت همه تیکت‌ها (فقط ادمین)",
     *     tags={"Support Tickets"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="لیست همه تیکت‌ها",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="subject", type="string", example="مشکل پرداخت"),
     *                 @OA\Property(property="status", type="string", example="open"),
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="کاربر تست")
     *                 ),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-05-09T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-05-09T12:00:00Z")
     *             )
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
     *         response=401,
     *         description="نیاز به احراز هویت",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function all(Request $request)
    {
        $this->authorize('viewAny', SupportTicket::class);
        return response()->json(SupportTicket::with('user')->latest()->get());
    }
}
