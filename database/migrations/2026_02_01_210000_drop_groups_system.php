<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * حذف نظام المجموعات بالكامل
     */
    public function up(): void
    {
        Schema::dropIfExists('group_user');
        Schema::dropIfExists('group_class');
        Schema::dropIfExists('group_subject');
        Schema::dropIfExists('groups');

        // حذف صلاحيات المجموعات
        $groupPermissionNames = ['group-list', 'group-create', 'group-edit', 'group-delete'];
        foreach ($groupPermissionNames as $name) {
            DB::table('role_has_permissions')
                ->whereIn('permission_id', DB::table('permissions')->where('name', $name)->pluck('id'))
                ->delete();
            DB::table('model_has_permissions')
                ->whereIn('permission_id', DB::table('permissions')->where('name', $name)->pluck('id'))
                ->delete();
            DB::table('permissions')->where('name', $name)->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('group_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('added_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['group_id', 'user_id']);
        });

        Schema::create('group_class', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('added_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['group_id', 'class_id']);
        });

        Schema::create('group_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('added_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['group_id', 'subject_id']);
        });
    }
};
