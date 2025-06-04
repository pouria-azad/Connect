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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('mobile_number')->unique();
            $table->string('username')->unique()->nullable();
            $table->string('full_name')->nullable();
            $table->string('national_code', 10)->unique()->nullable();
            $table->string('referral_code')->unique();
            $table->foreignId('referred_by_user_id')->nullable()->constrained('users');
            $table->enum('user_type', ['regular', 'provider']);
            $table->boolean('is_admin')->default(false);
            $table->timestamp('mobile_verified_at')->nullable();
            $table->string('password');
            $table->string('profile_image')->nullable();
            $table->text('bio')->nullable();
            $table->foreignId('province_id')->nullable()->constrained('provinces');
            $table->foreignId('city_id')->nullable()->constrained('cities');
            $table->boolean('can_serve_nation_wide')->default(false);
            $table->string('expertise_areas')->nullable();
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->integer('successful_orders_count')->default(0);
            $table->timestamp('last_activity_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
}; 