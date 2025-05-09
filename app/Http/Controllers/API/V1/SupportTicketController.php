<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    public function index(Request $request)
    {
        return response()->json($request->user()->supportTickets()->latest()->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject' => 'required|string|max:255',
        ]);

        $ticket = $request->user()->supportTickets()->create($data);

        return response()->json(['message' => 'تیکت ایجاد شد', 'data' => $ticket]);
    }

    public function show($id)
    {
        $ticket = SupportTicket::with('messages')->findOrFail($id);

        return response()->json($ticket);
    }

    public function all() // For admin
    {
        return response()->json(SupportTicket::with('user')->latest()->get());
    }
}

