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
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};
