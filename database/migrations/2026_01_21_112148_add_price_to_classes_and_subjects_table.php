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
        // إضافة حقول السعر إلى جدول classes
        Schema::table('classes', function (Blueprint $table) {
            if (!Schema::hasColumn('classes', 'price')) {
                $table->decimal('price', 10, 2)->default(0)->after('is_active')->comment('سعر الصف');
            }
            if (!Schema::hasColumn('classes', 'is_free')) {
                $table->boolean('is_free')->default(true)->after('price')->comment('هل الصف مجاني');
            }
        });

        // إضافة حقول السعر إلى جدول subjects
        Schema::table('subjects', function (Blueprint $table) {
            if (!Schema::hasColumn('subjects', 'price')) {
                $table->decimal('price', 10, 2)->default(0)->after('is_active')->comment('سعر المادة');
            }
            if (!Schema::hasColumn('subjects', 'is_free')) {
                $table->boolean('is_free')->default(true)->after('price')->comment('هل المادة مجانية');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            if (Schema::hasColumn('classes', 'is_free')) {
                $table->dropColumn('is_free');
            }
            if (Schema::hasColumn('classes', 'price')) {
                $table->dropColumn('price');
            }
        });

        Schema::table('subjects', function (Blueprint $table) {
            if (Schema::hasColumn('subjects', 'is_free')) {
                $table->dropColumn('is_free');
            }
            if (Schema::hasColumn('subjects', 'price')) {
                $table->dropColumn('price');
            }
        });
    }
};
