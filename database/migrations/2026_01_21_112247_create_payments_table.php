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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->comment('معرف الشراء');
            $table->enum('payment_method', ['stripe', 'paypal', 'wallet', 'iban', 'custom'])->comment('طريقة الدفع');
            $table->foreignId('custom_payment_method_id')->nullable()->comment('وسيلة الدفع المخصصة (إذا كانت custom)');
            $table->decimal('amount', 10, 2)->comment('المبلغ');
            $table->string('currency', 3)->default('SAR')->comment('العملة');
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending')->comment('حالة الدفع');
            $table->string('transaction_id')->nullable()->comment('معرف المعاملة من بوابة الدفع');
            $table->json('gateway_response')->nullable()->comment('استجابة بوابة الدفع');
            $table->string('receipt_file')->nullable()->comment('ملف الوصل (للدفع عبر IBAN)');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete()->comment('من راجع الدفع');
            $table->timestamp('reviewed_at')->nullable()->comment('تاريخ المراجعة');
            $table->text('review_notes')->nullable()->comment('ملاحظات المراجعة');
            $table->json('payment_data')->nullable()->comment('بيانات إضافية (IBAN, codes, etc.)');
            $table->timestamps();
            $table->softDeletes();
            
            // فهارس
            $table->index('purchase_id');
            $table->index('payment_method');
            $table->index('status');
            $table->index('transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
