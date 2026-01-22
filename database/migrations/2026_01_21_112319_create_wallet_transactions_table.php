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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained('wallets')->onDelete('cascade');
            $table->enum('type', ['deposit', 'withdrawal', 'purchase', 'refund'])->comment('نوع المعاملة');
            $table->decimal('amount', 10, 2)->comment('المبلغ');
            $table->decimal('balance_before', 10, 2)->comment('الرصيد قبل المعاملة');
            $table->decimal('balance_after', 10, 2)->comment('الرصيد بعد المعاملة');
            $table->text('description')->nullable()->comment('وصف المعاملة');
            $table->string('reference_type')->nullable()->comment('نوع المرجع (Purchase, Payment, etc.)');
            $table->unsignedBigInteger('reference_id')->nullable()->comment('معرف المرجع');
            $table->timestamps();
            
            // فهارس
            $table->index('wallet_id');
            $table->index('type');
            $table->index(['reference_type', 'reference_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
