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
        Schema::create('custom_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('اسم وسيلة الدفع');
            $table->enum('type', ['iban', 'code', 'other'])->comment('نوع وسيلة الدفع');
            $table->json('account_info')->nullable()->comment('معلومات الحساب (IBAN, account name, bank name, etc.)');
            $table->string('code_prefix')->nullable()->comment('بادئة الكود (للأكواد)');
            $table->text('instructions')->nullable()->comment('تعليمات الدفع');
            $table->boolean('requires_receipt')->default(true)->comment('هل يتطلب رفع وصل');
            $table->boolean('is_active')->default(true)->comment('هل نشط');
            $table->integer('order')->default(0)->comment('الترتيب');
            $table->timestamps();
            $table->softDeletes();
            
            // فهارس
            $table->index('type');
            $table->index('is_active');
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_payment_methods');
    }
};
