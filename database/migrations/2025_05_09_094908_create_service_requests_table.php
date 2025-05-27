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
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_user_id')->constrained('users')->onDelete('cascade'); // شناسه کاربری که درخواست را ثبت کرده است
            $table->foreignId('service_provider_user_id')->nullable()->constrained('users')->onDelete('set null'); // شناسه خدمات دهنده ای که درخواست برای او ارسال شده است (فقط برای درخواست خصوصی)
            $table->foreignId('service_category_id')->nullable()->constrained('service_categories')->onDelete('set null'); // شناسه دسته بندی خدمتی که کاربر انتخاب کرده است (فقط برای خصوصی)
            $table->string('subject'); // موضوع درخواست که کاربر وارد میکند
            $table->text('description'); // توضیحات کامل درخواست که توسط کاربر نوشته میشود
            $table->decimal('initial_fee_amount', 10, 2)->default(15000.00); // مبلغ ثابت ۱۵ هزار تومان برای ثبت درخواست
            $table->foreignId('invoice_id')->nullable(); // شناسه فاکتوری که برای پرداخت هزینه اولیه ثبت درخواست صادر شده است
            $table->foreignId('chat_id')->nullable(); // شناسه گفتگوی مرتبط با این درخواست پس از پذیرش درخواست ایجاد میشود

            $table->enum('status', [
                'pending', // وضعیت جدید برای تست‌ها
                'pending_payment', // قبل از پرداخت موفق کاربر
                'pending_admin_approval', // در انتظار تأیید مدیر بعد از پرداخت موفق کاربر
                'approved_by_admin', // توسط مدیر تأیید شده و به خدمات دهنده ارسال شده.
                'rejected_by_admin', // توسط مدیر رد شده است مبلغ بازگشت داده میشود.
                'pending_sp_acceptance', // در انتظار پذیرش توسط خدمات دهنده (برای عمومی)
                'accepted_by_sp', // توسط خدمات دهنده پذیرفته شده است.
                'rejected_by_sp', // توسط خدمات دهنده لغو شده است مبلغ بازگشت داده میشود.
                'canceled_by_customer', // توسط مشتری لغو شده اگر قبل از تأیید نهایی باشد.
                'completed', // درخواست با موفقیت انجام شده است.
                'expired', // جدید اگر هیچ SP ای در زمان مشخصی درخواست را نپذیرد.
                'ready_for_review', // وضعیت جدید برای ثبت نظر
            ])->default('pending_payment');

            $table->text('admin_notes')->nullable(); // یادداشتهای مدیر در مورد تأیید یا رد درخواست
            $table->text('rejection_reason')->nullable(); // دلیل لغو یا رد درخواست اگر توسط مدیر یا خدمات دهنده باشد

            $table->enum('request_type', ['private', 'public']); // نوع درخواست برای درخواست عمومی مقدار آن public خواهد بود
            $table->foreignId('province_id')->nullable()->constrained('provinces')->onDelete('set null'); // شناسه استان انتخابی کاربر برای درخواستهای عمومی
            $table->foreignId('city_id')->nullable()->constrained('cities')->onDelete('set null'); // شناسه شهر انتخابی کاربر برای درخواستهای عمومی
            $table->enum('scope_type', ['city_wide', 'nation_wide'])->nullable(); // محدوده ارسال درخواست عمومی (ارسال به شهر یا ارسال سراسری)
            $table->foreignId('accepted_service_provider_user_id')->nullable()->constrained('users')->onDelete('set null'); // شناسه خدمات دهنده ای که زودتر از بقیه این درخواست عمومی را پذیرفته است
            $table->timestamp('accepted_at')->nullable(); // زمان پذیرش درخواست توسط خدمات دهنده
            $table->timestamp('available_until')->nullable(); // زمانی که درخواست برای پذیرش توسط SP ها قابل دسترس است. مثلاً 24 ساعت پس از تایید مدیر تنظیم میشود
            $table->timestamp('completed_at')->nullable(); // زمان نهایی شدن و تکمیل درخواست

            $table->timestamps(); // created_at, updated_at
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
