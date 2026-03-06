<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\SystemSetting;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Remove platform reviews feature: drop platform_reviews table, remove setting, delete permissions.
     */
    public function up(): void
    {
        Schema::dropIfExists('platform_reviews');

        SystemSetting::where('key', 'platform_reviews_display_limit')->delete();

        $permissionNames = [
            'platform-reviews-list',
            'platform-reviews-edit',
            'platform-reviews-approve',
        ];
        $permissionIds = DB::table('permissions')->whereIn('name', $permissionNames)->pluck('id');
        if ($permissionIds->isNotEmpty()) {
            DB::table('role_has_permissions')->whereIn('permission_id', $permissionIds)->delete();
            DB::table('permissions')->whereIn('id', $permissionIds)->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('platform_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->unsignedTinyInteger('stars')->comment('1-5');
            $table->text('comment')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('photo')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index('user_id');
            $table->index('status');
            $table->index('order');
        });

        SystemSetting::set(
            'platform_reviews_display_limit',
            '6',
            'integer',
            'general',
            'عدد آراء الطلاب المعروضة في السلايدر بالصفحة الرئيسية'
        );

        // Permissions would need to be re-seeded
    }
};
