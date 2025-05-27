<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\Conversation;
use App\Models\User;
use App\Events\ChatMessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ChatMessageController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/chat-messages",
     *     summary="ارسال پیام جدید در یک مکالمه",
     *     tags={"Chat Messages"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent()),
     *     @OA\Response(response=201, description="پیام ارسال شد")
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'conversation_id' => 'required|exists:conversations,id',
            'content' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $conversation = Conversation::findOrFail($request->conversation_id);
        $user = Auth::user();

        // بررسی دسترسی به مکالمه
        if (!$conversation->participants()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'شما به این مکالمه دسترسی ندارید.'], 403);
        }

        // بررسی بلاک بودن
        $otherParticipant = $conversation->participants()
            ->where('user_id', '!=', $user->id)
            ->first();

        if ($otherParticipant && $user->hasBlocked($otherParticipant->user_id)) {
            return response()->json(['message' => 'شما این کاربر را بلاک کرده‌اید.'], 403);
        }

        if ($otherParticipant && $otherParticipant->user->hasBlocked($user->id)) {
            return response()->json(['message' => 'این کاربر شما را بلاک کرده است.'], 403);
        }

        $message = ChatMessage::create([
            'conversation_id' => $request->conversation_id,
            'sender_id' => $user->id,
            'content' => $request->content,
        ]);

        // ارسال پیام از طریق WebSocket
        broadcast(new ChatMessageSent($message))->toOthers();

        return response()->json([
            'message' => 'پیام با موفقیت ارسال شد.',
            'data' => $message->load('sender'),
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/chat-messages/{chatMessage}",
     *     summary="نمایش جزئیات یک پیام خاص",
     *     tags={"Chat Messages"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="chatMessage", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="جزئیات پیام")
     * )
     */
    public function show(ChatMessage $chatMessage)
    {
        $user = Auth::user();
        $conversation = $chatMessage->conversation;

        // بررسی دسترسی به مکالمه
        if (!$conversation->participants()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'شما به این مکالمه دسترسی ندارید.'], 403);
        }

        return response()->json([
            'data' => $chatMessage->load('sender'),
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/chat-messages/{chatMessage}",
     *     summary="ویرایش محتوای یک پیام",
     *     tags={"Chat Messages"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="chatMessage", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent()),
     *     @OA\Response(response=200, description="پیام با موفقیت ویرایش شد")
     * )
     */
    public function update(Request $request, ChatMessage $chatMessage)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();

        // بررسی مالکیت پیام
        if ($chatMessage->sender_id !== $user->id) {
            return response()->json(['message' => 'شما فقط می‌توانید پیام‌های خود را ویرایش کنید.'], 403);
        }

        $chatMessage->update([
            'content' => $request->content,
        ]);

        // ارسال پیام ویرایش شده از طریق WebSocket
        broadcast(new ChatMessageSent($chatMessage))->toOthers();

        return response()->json([
            'message' => 'پیام با موفقیت ویرایش شد.',
            'data' => $chatMessage->load('sender'),
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/chat-messages/{chatMessage}",
     *     summary="حذف یک پیام از مکالمه",
     *     tags={"Chat Messages"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="chatMessage", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="پیام با موفقیت حذف شد")
     * )
     */
    public function destroy(ChatMessage $chatMessage)
    {
        $user = Auth::user();

        // بررسی مالکیت پیام
        if ($chatMessage->sender_id !== $user->id) {
            return response()->json(['message' => 'شما فقط می‌توانید پیام‌های خود را حذف کنید.'], 403);
        }

        $chatMessage->delete();

        // ارسال پیام حذف شده از طریق WebSocket
        broadcast(new ChatMessageSent($chatMessage))->toOthers();

        return response()->json([
            'message' => 'پیام با موفقیت حذف شد.',
        ]);
    }
} 