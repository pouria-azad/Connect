<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('provider_canctyar')) {
            Schema::create('provider_canctyar', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('certification_number')->unique();
                $table->json('skills');
                $table->json('service_areas');
                $table->json('availability_hours');
                $table->boolean('can_travel')->default(true);
                $table->decimal('travel_fee_per_km', 10, 2)->default(0);
                $table->decimal('minimum_service_fee', 12, 2)->default(0);
                $table->json('portfolio_images')->nullable();
                $table->json('tags')->nullable();
                $table->boolean('is_verified')->default(false);
                $table->timestamp('verified_at')->nullable();
                $table->foreignId('verified_by_admin_id')->nullable()->constrained('users');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_canctyar');
    }
}; 