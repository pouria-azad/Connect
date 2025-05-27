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
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->text('content');
            $table->enum('type', [
                'text', // پیام متنی ساده
                'image', // تصویر
                'file', // فایل عمومی
                'voice', // پیام صوتی
                'system_message', // پیامی که توسط سیستم تولید شده مثلاً پرداخت موفق
                'ad_banner', // یک بنر تبلیغاتی که در چت نمایش داده میشود
                'invoice', // پیام حاوی فاکتور پرداخت
                'link_preview', // پیش نمایش لینک
                'payment', // پیام پرداخت
                'system', // پیام سیستمی
            ])->default('text');

            // Add file_url, file_name, file_size
            $table->string('file_url', 500)->nullable(); // آدرس URL فایل تصویر فایل صوت در فضای ذخیره‌سازی ابری
            $table->string('file_name')->nullable(); // نام اصلی فایل آپلود شده
            $table->integer('file_size')->nullable(); // حجم فایل آپلود شده به بایت

            // Add metadata for structured data (JSONB)
            $table->jsonb('metadata')->nullable(); // فیلد از نوع JSON برای ذخیره داده های ساختاریافته اضافی

            // Add status enum with more states
            $table->enum('status', [
                'sent', // پیام ارسال شده به سرور
                'delivered', // پیام به دستگاه گیرنده تحویل داده شده است.
                'read', // پیام توسط گیرنده خوانده شده است.
                'deleted_for_sender', // پیام فقط برای فرستنده حذف شده است.
                'deleted_for_all', // پیام برای هر دو طرف حذف شده است.
                'edited', // پیام ویرایش شده است.
            ])->default('sent'); // Changed from is_read boolean to status enum
            // Add read_at timestamp
            $table->timestamp('read_at')->nullable(); // زمان دقیق خوانده شدن پیام توسط گیرنده
            // Add fields for edit, reply, forward features
            $table->boolean('is_edited')->default(false); // TRUE اگر پیام پس از ارسال ویرایش شده باشد
            $table->timestamp('edited_at')->nullable(); // زمان آخرین ویرایش پیام
            $table->foreignId('replied_to_message_id')->nullable()->constrained('chat_messages')->onDelete('set null'); // شناسه پیام اصلی که به آن پاسخ داده شده
            $table->boolean('is_forwarded')->default(false); // TRUE اگر این پیام فوروارد شده از یک چت دیگر باشد
            $table->foreignId('original_message_id')->nullable()->constrained('chat_messages')->onDelete('set null'); // شناسه پیام اصلی را از جایی که فوروارد شده است

            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
