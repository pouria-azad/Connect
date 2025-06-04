<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    // لیست فاکتورهای کاربر جاری
    public function index(Request $request)
    {
        $invoices = Invoice::where('user_id', $request->user()->id)->latest()->paginate(20);
        return response()->json($invoices);
    }

    // نمایش جزئیات فاکتور
    public function show(Request $request, $id)
    {
        $invoice = Invoice::where('recipient_user_id', $request->user()->id)->with('items')->findOrFail($id);
        return response()->json($invoice);
    }

    // ایجاد فاکتور جدید
    public function store(Request $request)
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.title' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);
        $amount = collect($data['items'])->sum(function($item) {
            return $item['quantity'] * $item['unit_price'];
        });
        DB::beginTransaction();
        $invoice = Invoice::create([
            'sender_user_id' => $request->user()->id,
            'recipient_user_id' => $request->user()->id, // برای تست، گیرنده را خود کاربر قرار می‌دهیم
            'amount' => $amount,
            'final_amount' => $amount,
            'status' => 'pending',
            'invoice_number' => 'INV' . now()->format('YmdHis') . rand(100,999),
            'description' => $data['description'] ?? null,
        ]);
        foreach ($data['items'] as $item) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'title' => $item['title'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['quantity'] * $item['unit_price'],
            ]);
        }
        DB::commit();
        return response()->json($invoice->fresh('items'), 201);
    }

    // پرداخت فاکتور (شبیه‌سازی)
    public function pay(Request $request, $id)
    {
        $invoice = Invoice::where('recipient_user_id', $request->user()->id)->where('status', 'pending')->findOrFail($id);
        $invoice->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
        return response()->json(['message' => 'فاکتور با موفقیت پرداخت شد', 'invoice' => $invoice]);
    }
} 