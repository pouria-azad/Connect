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
        Schema::create('club_daily_credits', function (Blueprint $table) {
            $table->id();
            $table->date('credit_date')->unique(); // تاریخ مربوط به این اعتبار
            $table->decimal('amount', 18, 2); // مبلغ اعتبار برای آن روز
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('club_daily_credits');
    }
};
