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
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('provider_type', ['senfi', 'canctyar'])->required();
            $table->string('shop_name')->nullable();
            $table->string('senfi_number', 22)->unique()->nullable();
            $table->foreignId('occupation_id')->nullable()->constrained('occupations')->onDelete('set null');

            $table->text('bio')->nullable();
            $table->foreignId('province_id')->nullable()->constrained('provinces')->onDelete('set null');
            $table->foreignId('city_id')->nullable()->constrained('cities')->onDelete('set null');
            $table->boolean('can_serve_nation_wide')->default(false);
            $table->text('expertise_areas')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->string('profile_image')->nullable();
            $table->timestamp('last_activity_at')->nullable()->after('is_verified');
            $table->float('average_rating')->default(0);
            $table->unsignedInteger('successful_orders_count')->default(0);
            $table->timestamps();
        });

        // Create senfi providers table
        Schema::create('provider_senfi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->onDelete('cascade');
            $table->string('business_name');
            $table->string('business_license_number')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('business_address')->nullable();
            $table->string('business_phone')->nullable();
            $table->json('business_hours')->nullable();
            $table->json('accepted_payment_methods')->nullable();
            $table->boolean('has_physical_store')->default(false);
            $table->json('portfolio_images')->nullable();
            $table->json('tags')->nullable();
            $table->decimal('base_service_fee', 10, 2)->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by_admin_id')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        // Create canctyar providers table
        Schema::create('provider_canctyar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->onDelete('cascade');
            $table->string('certification_number')->nullable();
            $table->json('skills')->nullable();
            $table->json('service_areas')->nullable();
            $table->json('availability_hours')->nullable();
            $table->boolean('can_travel')->default(true);
            $table->decimal('travel_fee_per_km', 10, 2)->nullable();
            $table->decimal('minimum_service_fee', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('providers');
        Schema::dropIfExists('provider_senfi');
        Schema::dropIfExists('provider_canctyar');
    }
};
