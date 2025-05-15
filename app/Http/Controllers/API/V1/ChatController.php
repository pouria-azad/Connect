<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\ChatController\BlockUserRequest;
use App\Http\Requests\V1\ChatController\StoreChatMessageRequest;
use App\Http\Resources\V1\ChatMessageResource;
use App\Models\ChatMessage;
use App\Models\Conversation;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Events\MessageSent;

class ChatController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/chat/conversations/{conversationId}/messages",
     *     summary="لیست پیام‌های یک مکالمه با صفحه‌بندی",
     *     tags={"Chat"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="conversationId",
     *         in="path",
     *         description="شناسه مکالمه",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="شماره صفحه",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="موفقیت - لیست پیام‌ها",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(ref="#/components/schemas/ChatMessage")
     *             ),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="مکالمه یافت نشد"),
     *     @OA\Response(response=403, description="عدم دسترسی"),
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
                ->paginate(20);  // صفحه‌بندی، 20 پیام در هر صفحه

            return ChatMessageResource::collection($messages);

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Conversation not found'], 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/chat/send",
     *     summary="ارسال پیام جدید",
     *     tags={"Chat"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"conversation_id", "message"},
     *             @OA\Property(property="conversation_id", type="integer", example=1),
     *             @OA\Property(property="message", type="string", example="سلام!"),
     *             @OA\Property(property="type", type="string", enum={"text","system","invoice","payment"}, example="text")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="پیام با موفقیت ارسال شد",
     *         @OA\JsonContent(ref="#/components/schemas/ChatMessage")
     *     ),
     *     @OA\Response(response=403, description="شما در این مکالمه بلاک شده‌اید"),
     *     @OA\Response(response=404, description="مکالمه یافت نشد"),
     * )
     */
    public function send(StoreChatMessageRequest $req)
    {
        $data = $req->validated();

        try {
            $conversation = Conversation::findOrFail($data['conversation_id']);
            $this->authorize('view', $conversation);

            if (
                $conversation->users()
                    ->wherePivot('is_blocked', true)
                    ->where('user_id', $req->user()->id)
                    ->exists()
            ) {
                return response()->json(['message' => 'You are blocked in this conversation'], 403);
            }

            $data['content'] = $data['message'];
            unset($data['message']);

            if (!isset($data['type'])) {
                $data['type'] = 'text';
            }

            $data['sender_id'] = $req->user()->id;

            $msg = ChatMessage::create($data);

            broadcast(new MessageSent($msg))->toOthers();

            return (new ChatMessageResource($msg))->response()->setStatusCode(201);

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Conversation not found'], 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/chat/conversations",
     *     summary="ساخت مکالمه جدید",
     *     tags={"Chat"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"order_id", "user_ids"},
     *             @OA\Property(property="order_id", type="integer", example=1),
     *             @OA\Property(property="user_ids", type="array",
     *                 @OA\Items(type="integer", example=2)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="مکالمه با موفقیت ساخته شد",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="order_id", type="integer", example=1),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time"),
     *         )
     *     ),
     *     @OA\Response(response=422, description="خطا در اعتبارسنجی داده‌ها"),
     * )
     */
    public function createConversation(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $conversation = Conversation::create([
            'order_id' => $request->order_id,
        ]);

        // اضافه کردن خود کاربر و کاربران هدف به مکالمه
        $users = array_unique(array_merge([$request->user()->id], $request->user_ids));

        $conversation->users()->attach($users);

        return response()->json($conversation, 201);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/chat/conversations/{conversationId}/mark-read",
     *     summary="علامت‌گذاری پیام‌ها به عنوان خوانده شده",
     *     tags={"Chat"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="conversationId",
     *         in="path",
     *         description="شناسه مکالمه",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=204, description="موفقیت - بدون محتوا"),
     *     @OA\Response(response=404, description="مکالمه یافت نشد"),
     * )
     */
    public function markRead(Request $req, $conversationId)
    {
        $user = $req->user();

        ChatMessage::where('conversation_id', $conversationId)
            ->where('sender_id', '!=', $user->id)
            ->update(['is_read' => true]);

        return response()->noContent();
    }

    /**
     * @OA\Post(
     *     path="/api/v1/chat/block",
     *     summary="بلاک کردن کاربر در مکالمه",
     *     tags={"Chat"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/BlockUserRequest")
     *     ),
     *     @OA\Response(response=204, description="موفقیت - بدون محتوا"),
     *     @OA\Response(response=404, description="مکالمه یافت نشد"),
     * )
     */
    public function block(BlockUserRequest $req)
    {
        try {
            $conv = Conversation::findOrFail($req->conversation_id);
            $this->authorize('update', $conv);

            $currentStatus = $conv->users()->where('user_id', $req->user_id)->first()?->pivot->is_blocked;

            if ($currentStatus === true) {
                return response()->json(['message' => 'User already blocked'], 200);
            }

            $conv->users()->updateExistingPivot($req->user_id, ['is_blocked' => true]);

            return response()->noContent();
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Conversation not found'], 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/chat/unblock",
     *     summary="آنبلاک کردن کاربر در مکالمه",
     *     tags={"Chat"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/BlockUserRequest")
     *     ),
     *     @OA\Response(response=204, description="موفقیت - بدون محتوا"),
     *     @OA\Response(response=404, description="مکالمه یافت نشد"),
     * )
     */
    public function unblock(BlockUserRequest $req)
    {
        try {
            $conv = Conversation::findOrFail($req->conversation_id);
            $this->authorize('update', $conv);

            $currentStatus = $conv->users()->where('user_id', $req->user_id)->first()?->pivot->is_blocked;

            if ($currentStatus === false) {
                return response()->json(['message' => 'User already unblocked'], 200);
            }

            $conv->users()->updateExistingPivot($req->user_id, ['is_blocked' => false]);

            return response()->noContent();
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Conversation not found'], 404);
        }
    }
}
