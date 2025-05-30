<?php
namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\SupportTicket\storeSupportTicketRequest;
use App\Models\SupportTicket;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\Notification;

class SupportTicketController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/support/tickets",
     *     summary="لیست تیکت‌های پشتیبانی (User)",
     *     tags={"SupportTicket (User)"},
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
    public function index()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }
        $tickets = $user->supportTickets()->latest()->get();
        return response()->json($tickets);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/support/tickets",
     *     summary="ثبت تیکت پشتیبانی جدید (User)",
     *     tags={"SupportTicket (User)"},
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
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }
        $request->validate([
            'subject' => 'required|string|max:255'
        ]);
        $ticket = \App\Models\SupportTicket::create([
            'user_id' => $user->id,
            'subject' => $request->subject,
            'status' => 'open'
        ]);
        return response()->json([
            'message' => 'تیکت ایجاد شد',
            'data' => $ticket
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/support/tickets/{id}",
     *     summary="نمایش جزئیات تیکت پشتیبانی (User)",
     *     tags={"SupportTicket (User)"},
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
    public function show($id)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }
        $ticket = \App\Models\SupportTicket::find($id);
        if (!$ticket) {
            return response()->json([
                'message' => 'تیکت یافت نشد'
            ], 404);
        }
        if ($ticket->user_id !== $user->id) {
            return response()->json([
                'message' => 'This action is unauthorized.'
            ], 403);
        }
        return response()->json($ticket);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/support/tickets/all",
     *     summary="دریافت همه تیکت‌ها (فقط ادمین)",
     *     tags={"Support Tickets (Admin)"},
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
    public function adminIndex()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }
        if (!$user->is_admin) {
            return response()->json([
                'message' => 'This action is unauthorized.'
            ], 403);
        }
        $tickets = \App\Models\SupportTicket::latest()->get();
        return response()->json($tickets);
    }
}
