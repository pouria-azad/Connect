<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // کاربری که باید اعلان را دریافت کند
            $table->enum('type', [ // نوع اعلان
                'new_message', // پیام جدید
                'invoice_paid', // فاکتور پرداخت شد
                'chat_expired', // چت منقضی شد
                'admin_message', // پیام مدیر
                'order_status_update', // بروزرسانی وضعیت سفارش
                'transaction', // اعلان‌های تراکنش
                'request_status', // وضعیت درخواست خدمات
                'review', // نظر جدید
            ]);
            $table->string('title'); // عنوان اعلان
            $table->text('body'); // متن اصلی اعلان
            $table->jsonb('data')->nullable(); // داده‌های مرتبط با اعلان (JSONB)
            $table->boolean('is_read')->default(false); // وضعیت خوانده شدن اعلان توسط کاربر
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium'); // برای مدیریت اولویت ارسال Push Notification
            $table->timestamps(); // sent_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
