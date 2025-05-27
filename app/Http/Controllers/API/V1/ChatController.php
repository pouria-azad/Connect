<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\ChatController\BlockUserRequest;
use App\Http\Requests\V1\ChatController\StoreChatMessageRequest;
use App\Http\Resources\V1\ChatMessageResource;
use App\Models\ChatMessage;
use App\Models\Conversation;
use App\Models\Notification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Events\MessageSent;

class ChatController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/conversations/{conversation}/messages",
     *     summary="دریافت پیام‌های یک مکالمه با صفحه‌بندی",
     *     tags={"Chat"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="conversation", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="لیست پیام‌ها")
     * )
     */
    public function messages(Request $req, $conversationId)
    {
        try {
            $conversation = Conversation::with('users')->findOrFail($conversationId);
            $this->authorize('view', $conversation);

            $messages = $conversation->messages()
                ->with('sender')
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return ChatMessageResource::collection($messages);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'مکالمه یافت نشد'], 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/chat/send",
     *     summary="ارسال پیام جدید در یک مکالمه",
     *     tags={"Chat"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent()),
     *     @OA\Response(response=201, description="پیام ارسال شد")
     * )
     */
    public function send(StoreChatMessageRequest $req)
    {
        $data = $req->validated();

        try {
            $conversation = Conversation::findOrFail($data['conversation_id']);
            $this->authorize('view', $conversation);

            // بررسی وضعیت بلاک بودن کاربر
            $pivot = $conversation->users()->where('user_id', $req->user()->id)->first()->pivot;
            if ($pivot->is_blocked) {
                return response()->json(['message' => 'شما در این مکالمه بلاک شده‌اید'], 403);
            }

            // ذخیره پیام با فیلدهای کامل
            $msg = ChatMessage::create([
                'conversation_id' => $data['conversation_id'],
                'sender_id' => $req->user()->id,
                'content' => $data['content'],
                'type' => $data['type'] ?? 'text',
                'file_url' => $data['file_url'] ?? null,
                'file_name' => $data['file_name'] ?? null,
                'file_size' => $data['file_size'] ?? null,
                'metadata' => $data['metadata'] ?? null,
                'status' => 'sent',
            ]);

            // به‌روزرسانی last_message_at در جدول conversations
            $conversation->last_message_at = now();
            $conversation->save();

            // ارسال اعلان به کاربر گیرنده
            $receiverId = $conversation->users()->where('user_id', '!=', $req->user()->id)->first()->id;
            Notification::create([
                'user_id' => $receiverId,
                'type' => 'new_message',
                'title' => 'پیام جدید',
                'body' => 'شما یک پیام جدید دارید.',
                'data' => json_encode(['conversation_id' => $conversation->id, 'message_id' => $msg->id]),
            ]);

            // پخش رویداد پیام ارسال‌شده
            broadcast(new MessageSent($msg))->toOthers();

            return (new ChatMessageResource($msg))->response()->setStatusCode(201);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'مکالمه یافت نشد'], 404);
        }
    }

    /**
     * ایجاد یک مکالمه جدید بین کاربران.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createConversation(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        // ایجاد مکالمه جدید
        $conversation = Conversation::create([
            'order_id' => $request->order_id,
            'user1_id' => $request->user()->id,
            'user2_id' => $request->user_ids[0], // فرض بر یک کاربر مقصد
            'type' => 'direct', // یا 'service_request' بر اساس نیاز
            'status' => 'open',
            'expires_at' => now()->addDays(30),
            'last_message_at' => now(),
        ]);

        // اضافه کردن کاربران به جدول پیوت
        $users = array_unique([$request->user()->id, $request->user_ids[0]]);
        $conversation->users()->attach($users);

        return response()->json($conversation, 201);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/conversations/{conversation}/read",
     *     summary="علامت‌گذاری پیام‌های یک مکالمه به عنوان خوانده‌شده",
     *     tags={"Chat"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="conversation", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="پیام‌ها خوانده شدند")
     * )
     */
    public function markRead(Request $req, $conversationId)
    {
        $user = $req->user();

        ChatMessage::where('conversation_id', $conversationId)
            ->where('sender_id', '!=', $user->id)
            ->update([
                'status' => 'read',
                'read_at' => now(),
                'is_read' => true,
            ]);

        return response()->noContent();
    }

    /**
     * @OA\Post(
     *     path="/api/v1/conversations/{conversation}/block",
     *     summary="بلاک کردن یک کاربر در مکالمه",
     *     tags={"Chat"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="conversation", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent()),
     *     @OA\Response(response=204, description="کاربر بلاک شد")
     * )
     */
    public function block(BlockUserRequest $req)
    {
        try {
            $conv = Conversation::findOrFail($req->conversation_id);
            $this->authorize('update', $conv);

            // بلاک کردن کاربر
            $conv->users()->updateExistingPivot($req->user_id, ['is_blocked' => true]);

            // به‌روزرسانی وضعیت مکالمه
            $conv->status = 'closed_by_user';
            $conv->save();

            // ارسال اعلان به کاربر بلاک‌شده
            Notification::create([
                'user_id' => $req->user_id,
                'type' => 'admin_message',
                'title' => 'بلاک شدن در مکالمه',
                'body' => 'شما در مکالمه بلاک شده‌اید.',
                'data' => json_encode(['conversation_id' => $conv->id]),
            ]);

            return response()->noContent();
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'مکالمه یافت نشد'], 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/conversations/{conversation}/unblock",
     *     summary="آنبلاک کردن یک کاربر در مکالمه",
     *     tags={"Chat"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="conversation", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent()),
     *     @OA\Response(response=204, description="کاربر آنبلاک شد")
     * )
     */
    public function unblock(BlockUserRequest $req)
    {
        try {
            $conv = Conversation::findOrFail($req->conversation_id);
            $this->authorize('update', $conv);

            // آنبلاک کردن کاربر
            $conv->users()->updateExistingPivot($req->user_id, ['is_blocked' => false]);

            // به‌روزرسانی وضعیت مکالمه
            $conv->status = 'open';
            $conv->save();

            return response()->noContent();
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'مکالمه یافت نشد'], 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/conversations/{conversation}/block",
     *     summary="بلاک کردن کاربر (آلیاس)",
     *     tags={"Chat"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="conversation", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent()),
     *     @OA\Response(response=204, description="کاربر بلاک شد")
     * )
     */
    public function blockUser(\App\Http\Requests\V1\ChatController\BlockUserRequest $request, $conversation)
    {
        return $this->block($request);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/conversations/{conversation}/unblock",
     *     summary="آنبلاک کردن کاربر (آلیاس)",
     *     tags={"Chat"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="conversation", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent()),
     *     @OA\Response(response=204, description="کاربر آنبلاک شد")
     * )
     */
    public function unblockUser(\App\Http\Requests\V1\ChatController\BlockUserRequest $request, $conversation)
    {
        return $this->unblock($request);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/chat/send",
     *     summary="ارسال پیام (آلیاس)",
     *     tags={"Chat"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent()),
     *     @OA\Response(response=201, description="پیام ارسال شد")
     * )
     */
    public function sendMessage(\App\Http\Requests\V1\ChatController\StoreChatMessageRequest $request)
    {
        return $this->send($request);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/conversations/{conversation}/read",
     *     summary="علامت‌گذاری پیام‌ها به عنوان خوانده‌شده (آلیاس)",
     *     tags={"Chat"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="conversation", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="پیام‌ها خوانده شدند")
     * )
     */
    public function markAsRead(Request $request, $conversation)
    {
        return $this->markRead($request, $conversation);
    }
}
