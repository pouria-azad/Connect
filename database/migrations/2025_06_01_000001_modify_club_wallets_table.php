<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('club_wallets', function (Blueprint $table) {
            // Drop the unique constraint on user_id
            $table->dropUnique(['user_id']);
            
            // Modify existing columns
            $table->decimal('balance', 10, 2)->default(0)->change();
            
            // Drop the old column
            $table->dropColumn('last_credit_received_at');
            
            // Add new columns
            $table->decimal('total_earned', 10, 2)->default(0);
            $table->decimal('total_spent', 10, 2)->default(0);
            $table->timestamp('last_transaction_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('club_wallets', function (Blueprint $table) {
            // Revert column changes
            $table->decimal('balance', 18, 2)->default(0.00)->change();
            
            // Drop new columns
            $table->dropColumn(['total_earned', 'total_spent', 'last_transaction_at']);
            
            // Add back old column
            $table->date('last_credit_received_at')->nullable();
            
            // Add back unique constraint
            $table->unique('user_id');
        });
    }
}; 