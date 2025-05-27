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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->nullable(); // شناسه گفتگویی که فاکتور در آن ارسال شده است
            $table->foreignId('request_id')->nullable(); // شناسه درخواست خدمتی که این فاکتور برای آن صادر شده است برای هزینه اولیه ثبت درخواست
            $table->foreignId('sender_user_id')->constrained('users')->onDelete('cascade'); // شناسه خدمات دهنده ای که فاکتور را صادر کرده است در این حالت سیستم
            $table->foreignId('recipient_user_id')->constrained('users')->onDelete('cascade'); // شناسه کاربری که فاکتور برای او صادر شده است مشتری
            $table->decimal('amount', 18, 2); // مبلغ اصلی فاکتور
            $table->decimal('final_amount', 18, 2); // مبلغ نهایی پس از اعمال تخفیف
            $table->foreignId('discount_code_id')->nullable(); // اگر کد تخفیفی استفاده شده باشد
            $table->enum('status', ['pending', 'paid', 'canceled', 'refunded'])->default('pending'); // وضعیت پرداخت فاکتور
            $table->string('invoice_number', 50)->unique(); // شماره فاکتور یکتا
            $table->timestamp('paid_at')->nullable(); // زمان پرداخت
            $table->enum('payment_method', ['wallet', 'gateway'])->nullable(); // روش پرداخت
            $table->string('gateway_transaction_id', 255)->nullable(); // شناسه تراکنش در سیستم درگاه پرداخت
            $table->timestamps(); // created_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
