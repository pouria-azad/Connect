<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('referral_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('new_user_id')->constrained('users')->onDelete('cascade'); // کاربر معرفی شده
            $table->foreignId('referrer_user_id')->constrained('users')->onDelete('cascade'); // کاربر معرف
            $table->decimal('bonus_amount_per_user', 18, 2); // مبلغ پاداش برای هر طرف
            $table->timestamp('referral_date')->default(DB::raw('CURRENT_TIMESTAMP')); // زمان معرفی
            // لینک به تراکنش‌های WalletTransactions واریز پاداش کاربر جدید و معرف
            $table->foreignId('new_user_wallet_transaction_id')->nullable()->constrained('wallet_transactions')->onDelete('set null');
            $table->foreignId('referrer_user_wallet_transaction_id')->nullable()->constrained('wallet_transactions')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_transactions');
    }
};
