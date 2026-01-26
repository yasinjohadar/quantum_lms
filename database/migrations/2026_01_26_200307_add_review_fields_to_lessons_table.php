<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->enum('review_status', ['draft', 'pending_review', 'approved', 'rejected'])
                  ->default('draft')
                  ->after('is_active')
                  ->comment('حالة مراجعة الدرس');
            $table->text('review_notes')->nullable()->after('review_status')->comment('ملاحظات المشرف');
            $table->unsignedBigInteger('reviewed_by')->nullable()->after('review_notes')->comment('المشرف الذي راجع');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by')->comment('تاريخ المراجعة');
            $table->timestamp('submitted_for_review_at')->nullable()->after('reviewed_at')->comment('تاريخ إرسال الدرس للمراجعة');
            
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
        });

        // تحديث الدروس الموجودة
        DB::table('lessons')->where('is_active', true)->update(['review_status' => 'approved']);
        DB::table('lessons')->where('is_active', false)->update(['review_status' => 'draft']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn([
                'review_status',
                'review_notes',
                'reviewed_by',
                'reviewed_at',
                'submitted_for_review_at'
            ]);
        });
    }
};
