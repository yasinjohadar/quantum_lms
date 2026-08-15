<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('learning_experiences', function (Blueprint $table) {
            $table->text('review_notes')->nullable()->after('status')->comment('ملاحظات المشرف');
            $table->unsignedBigInteger('reviewed_by')->nullable()->after('review_notes')->comment('المشرف الذي راجع');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by')->comment('تاريخ المراجعة');
            $table->timestamp('submitted_for_review_at')->nullable()->after('reviewed_at')->comment('تاريخ إرسال الاختبار التفاعلي للمراجعة');

            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('learning_experiences', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn([
                'review_notes',
                'reviewed_by',
                'reviewed_at',
                'submitted_for_review_at',
            ]);
        });
    }
};
