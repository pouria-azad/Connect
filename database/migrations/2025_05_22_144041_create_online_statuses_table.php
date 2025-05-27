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
        Schema::create('online_statuses', function (Blueprint $table) {
            $table->foreignId('user_id')->primary()->constrained('users')->onDelete('cascade'); // شناسه کاربری این فیلد همزمان کلید اصلی این جدول است
            $table->boolean('is_online')->default(false); // TRUE اگر کاربر در حال حاضر آنلاین باشد
            $table->timestamp('last_active_at'); // زمان آخرین فعالیت کاربر
            $table->timestamp('last_typing_at')->nullable(); // زمان آخرین باری که کاربر شروع به تایپ کردن کرده است
            $table->timestamps(); // Created at, updated at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('online_statuses');
    }
};
