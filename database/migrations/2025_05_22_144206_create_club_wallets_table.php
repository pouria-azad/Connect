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
        Schema::create('club_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade'); // شناسه کاربری که این صندوق به او تعلق دارد
            $table->decimal('balance', 18, 2)->default(0.00); // موجودی فعلی صندوق کلاب
            $table->date('last_credit_received_at')->nullable(); // تاریخ آخرین باری که کاربر اعتبار روزانه را دریافت کرده است
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('club_wallets');
    }
};
