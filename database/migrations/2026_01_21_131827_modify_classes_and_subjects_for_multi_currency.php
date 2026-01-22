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
        Schema::table('classes', function (Blueprint $table) {
            $table->foreignId('default_currency_id')->nullable()->after('is_free')->constrained('currencies')->nullOnDelete()->comment('العملة الافتراضية');
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->foreignId('default_currency_id')->nullable()->after('is_free')->constrained('currencies')->nullOnDelete()->comment('العملة الافتراضية');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropForeign(['default_currency_id']);
            $table->dropColumn('default_currency_id');
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropForeign(['default_currency_id']);
            $table->dropColumn('default_currency_id');
        });
    }
};
