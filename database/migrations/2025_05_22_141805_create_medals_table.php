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
        Schema::create('medals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->onDelete('cascade');
            $table->string('title')->nullable();
            $table->timestamps();
        });

        Schema::create('service_provider_medals', function (Blueprint $table) {
            $table->foreignId('service_provider_user_id')->constrained('users')->onDelete('cascade'); // شناسه خدمات دهنده
            $table->foreignId('medal_id')->constrained('medals')->onDelete('cascade'); // شناسه مدال فعال شده
            $table->timestamp('awarded_at')->default(DB::raw('CURRENT_TIMESTAMP')); // زمان اعطای مدال
            $table->boolean('is_active')->default(true); // نشان میدهد که آیا این مدال در حال حاضر فعال است یا خیر
            $table->jsonb('criteria_snapshot')->nullable(); // اختیاری ذخیره وضعیت معیارهایی که باعث فعال شدن مدال شده اند
            $table->primary(['service_provider_user_id', 'medal_id']); // کلید اصلی ترکیبی
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_provider_medals');
        Schema::dropIfExists('medals');
    }
};
