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
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_currency_id')->constrained('currencies')->onDelete('cascade')->comment('من عملة');
            $table->foreignId('to_currency_id')->constrained('currencies')->onDelete('cascade')->comment('إلى عملة');
            $table->decimal('rate', 15, 6)->comment('سعر الصرف');
            $table->boolean('is_active')->default(true)->comment('هل سعر الصرف نشط');
            $table->timestamps();
            $table->softDeletes();
            $table->index('from_currency_id');
            $table->index('to_currency_id');
            $table->index('is_active');
            $table->unique(['from_currency_id', 'to_currency_id'], 'unique_exchange_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
