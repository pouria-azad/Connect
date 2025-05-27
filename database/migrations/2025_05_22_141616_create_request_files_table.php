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
        Schema::create('request_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('service_requests')->onDelete('cascade'); // شناسه درخواستی که این فایل به آن مرتبط است
            $table->string('file_url', 500); // URL آدرس فایل در فضای ذخیره سازی ابری
            $table->string('file_name'); // نام اصلی فایل
            $table->integer('file_size'); // حجم فایل به بایت حداکثر ۱۰ مگابایت
            $table->string('file_type', 50)->nullable(); // نوع MIME فایل (مثلاً image/jpeg, application/pdf)
            $table->timestamps(); // uploaded_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_files');
    }
};
