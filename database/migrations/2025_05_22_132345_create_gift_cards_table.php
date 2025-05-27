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
        Schema::create('gift_cards', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique(); // کد یکتای کارت هدیه برای وارد کردن توسط کاربر
            $table->decimal('initial_balance', 18, 2); // مبلغ اولیه کارت هدیه
            $table->decimal('current_balance', 18, 2); // موجودی فعلی کارت هدیه
            $table->timestamp('expires_at'); // تاریخ انقضای کارت هدیه
            $table->boolean('is_used')->default(false); // آیا کارت هدیه استفاده شده است؟
            $table->boolean('is_active')->default(true); // وضعیت فعال بودن کارت هدیه
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->onDelete('set null'); // کاربری که کارت هدیه را ایجاد کرده
            $table->foreignId('used_by_user_id')->nullable()->constrained('users')->onDelete('set null'); // کاربری که کارت هدیه را استفاده کرده
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->onDelete('set null'); // شناسه کاربری که کارت هدیه به او تعلق گرفته است اگر شخصی سازی شده باشد
            $table->foreignId('redeemed_by_user_id')->nullable()->constrained('users')->onDelete('set null'); // شناسه کاربری که کارت هدیه را استفاده کرده است در صورت اولین استفاده
            $table->timestamp('redeemed_at')->nullable(); // زمان اولین استفاده از کارت هدیه
            $table->enum('source_type', ['admin_issued', 'club_wallet_conversion'])->default('admin_issued'); // منبع کارت هدیه مثلاً صادر شده توسط ادمین یا تبدیل شده از صندوق کلاب
            $table->timestamps(); // created_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gift_cards');
    }
};
