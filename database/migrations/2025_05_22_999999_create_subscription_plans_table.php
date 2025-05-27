<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('duration_days');
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('max_ads_count')->default(0);
            $table->integer('max_services_count')->default(0);
            $table->integer('priority_level')->default(0);
            $table->boolean('can_highlight_ads')->default(false);
            $table->boolean('can_pin_ads')->default(false);
            $table->boolean('can_use_advanced_features')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
}; 