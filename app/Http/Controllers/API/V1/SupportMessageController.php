<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Illuminate\Http\Request;

class SupportMessageController extends Controller
{
    public function replyUser(Request $request, $id)
    {
        $ticket = SupportTicket::findOrFail($id);

        $request->validate([
            'message' => 'required|string',
        ]);

        $ticket->messages()->create([
            'message' => $request->message,
            'user_id' => $request->user()->id,
            'is_admin' => false,
        ]);

        $ticket->update(['status' => 'open']);

        return response()->json(['message' => 'پیام ارسال شد']);
    }

    public function replyAdmin(Request $request, $id)
    {
        $ticket = SupportTicket::findOrFail($id);

        $request->validate([
            'message' => 'required|string',
        ]);

        $ticket->messages()->create([
            'message' => $request->message,
            'user_id' => null,
            'is_admin' => true,
        ]);

        $ticket->update(['status' => 'answered']);

        return response()->json(['message' => 'پاسخ ثبت شد']);
    }
}
