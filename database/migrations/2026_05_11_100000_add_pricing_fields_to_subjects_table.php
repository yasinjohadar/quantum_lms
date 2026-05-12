<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->boolean('is_free_override')->default(false)->after('is_free')
                  ->comment('تجاوز مجانية الصف - جعل المادة مجانية داخل صف مدفوع');
            $table->boolean('can_purchase_separately')->default(true)->after('is_free_override')
                  ->comment('السماح بشراء المادة بشكل منفصل');
            $table->boolean('show_price')->default(true)->after('can_purchase_separately')
                  ->comment('إظهار السعر في الواجهة الأمامية');
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn(['is_free_override', 'can_purchase_separately', 'show_price']);
        });
    }
};
