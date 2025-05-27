<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('service_provider_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('provider_id')->nullable()->constrained('providers')->onDelete('cascade');
            $table->integer('rating')->comment('امتیاز از 1 تا 5');
            $table->text('comment')->nullable();
            $table->json('rating_details')->nullable()->comment('جزئیات امتیازدهی در بخش‌های مختلف');
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_visible')->default(true);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // هر کاربر فقط یک نظر برای هر درخواست سرویس می‌تواند ثبت کند
            $table->unique(['service_request_id', 'customer_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
}; 