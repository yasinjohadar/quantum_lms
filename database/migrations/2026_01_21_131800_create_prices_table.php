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
        Schema::create('prices', function (Blueprint $table) {
            $table->id();
            $table->morphs('pricable'); // pricable_type, pricable_id (Class/Subject)
            $table->foreignId('currency_id')->constrained('currencies')->onDelete('cascade');
            $table->decimal('price', 10, 2)->comment('السعر');
            $table->boolean('is_active')->default(true)->comment('هل السعر نشط');
            $table->timestamps();
            $table->softDeletes();
            $table->index('currency_id');
            $table->index('is_active');
            $table->unique(['pricable_type', 'pricable_id', 'currency_id'], 'unique_price_per_item_currency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prices');
    }
};
