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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->morphs('purchasable'); // purchasable_type, purchasable_id
            $table->enum('purchase_type', ['class', 'subject'])->comment('نوع الشراء: صف أو مادة');
            $table->decimal('price', 10, 2)->comment('السعر وقت الشراء');
            $table->enum('status', ['pending', 'completed', 'cancelled', 'refunded'])->default('pending')->comment('حالة الشراء');
            $table->timestamp('purchased_at')->nullable()->comment('تاريخ الشراء');
            $table->timestamp('expires_at')->nullable()->comment('تاريخ انتهاء الصلاحية (اختياري)');
            $table->text('notes')->nullable()->comment('ملاحظات');
            $table->timestamps();
            $table->softDeletes();
            
            // فهارس
            $table->index('user_id');
            // morphs ينشئ فهرس تلقائياً
            $table->index('status');
            $table->index('purchased_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
