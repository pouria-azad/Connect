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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user1_id')->nullable()->constrained('users')->onDelete('set null');// شناسه کاربر اول در گفتگو
            $table->foreignId('user2_id')->nullable()->constrained('users')->onDelete('set null'); // شناسه کاربر دوم در گفتگو
            // Add type for conversation
            $table->enum('type', ['direct', 'support', 'service_request'])->default('direct'); // نوع این مکالمه
            // Add status for conversation
            $table->enum('status', ['open', 'closed_by_time', 'closed_by_user', 'closed_by_admin', 'pending_payment'])->default('open'); // وضعیت فعلی گفتگو
            // Add expires_at
            $table->timestamp('expires_at')->nullable(); // تاریخ و زمان انقضای گفتگو
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
