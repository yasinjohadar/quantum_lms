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
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique()->comment('رمز العملة (SYP, USD, TRY)');
            $table->string('name')->comment('اسم العملة');
            $table->string('symbol')->comment('رمز العملة (₺, $, ل.س)');
            $table->boolean('is_default')->default(false)->comment('هل هي العملة الافتراضية');
            $table->boolean('is_active')->default(true)->comment('هل العملة نشطة');
            $table->integer('order')->default(0)->comment('ترتيب العرض');
            $table->timestamps();
            $table->softDeletes();
            $table->index('code');
            $table->index('is_default');
            $table->index('is_active');
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
