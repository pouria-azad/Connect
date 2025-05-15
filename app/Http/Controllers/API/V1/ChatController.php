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
     *     path="/api/v1/chat/messages/{conversationId}",
     *     tags={"Chat"},
     *     summary="لیست پیام‌های یک مکالمه",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="conversationId",
     *         in="path",
     *         required=true,
     *         description="شناسه مکالمه",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="لیست پیام‌ها",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/ChatMessageResource"))
     *     ),
     *     @OA\Response(response=404, description="مکالمه پیدا نشد")
     * )
     */

    public function messages(Request $req, $conversationId)
    {
        try {
            $conversation = Conversation::with('users')->findOrFail($conversationId);
            $this->authorize('view', $conversation);

            return ChatMessageResource::collection(
                $conversation->messages()->with('sender')->get()
            );
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Conversation not found'], 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/chat/send",
     *     tags={"Chat"},
     *     summary="ارسال پیام",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreChatMessageRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="پیام ارسال شد",
     *         @OA\JsonContent(ref="#/components/schemas/ChatMessageResource")
     *     ),
     *     @OA\Response(response=403, description="کاربر بلاک شده است"),
     *     @OA\Response(response=404, description="مکالمه پیدا نشد")
     * )
     */

    public function send(StoreChatMessageRequest $req)
    {
        $data = $req->validated();

        try {
            $conversation = Conversation::findOrFail($data['conversation_id']);
            $this->authorize('view', $conversation);

            // Check if current user is blocked
            if (
                $conversation->users()
                    ->wherePivot('is_blocked', true)
                    ->where('user_id', $req->user()->id)
                    ->exists()
            ) {
                return response()->json(['message' => 'You are blocked in this conversation'], 403);
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
     *     path="/api/v1/chat/read/{conversationId}",
     *     tags={"Chat"},
     *     summary="علامت‌گذاری پیام‌ها به عنوان خوانده شده",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="conversationId",
     *         in="path",
     *         required=true,
     *         description="شناسه مکالمه",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="موفقیت بدون پاسخ")
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
     *     tags={"Chat"},
     *     summary="بلاک کردن کاربر",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/BlockUserRequest")
     *     ),
     *     @OA\Response(response=204, description="کاربر با موفقیت بلاک شد"),
     *     @OA\Response(response=200, description="کاربر قبلاً بلاک شده است"),
     *     @OA\Response(response=404, description="مکالمه پیدا نشد")
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
     *     tags={"Chat"},
     *     summary="آنبلاک کردن کاربر",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/BlockUserRequest")
     *     ),
     *     @OA\Response(response=204, description="کاربر با موفقیت آنبلاک شد"),
     *     @OA\Response(response=200, description="کاربر قبلاً آنبلاک شده است"),
     *     @OA\Response(response=404, description="مکالمه پیدا نشد")
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
