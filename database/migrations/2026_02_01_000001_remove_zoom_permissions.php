<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Remove Zoom permissions from permissions table and role_has_permissions.
     */
    public function up(): void
    {
        $permissionIds = DB::table('permissions')->where('name', 'like', 'zoom-%')->pluck('id');

        if ($permissionIds->isNotEmpty()) {
            DB::table('role_has_permissions')->whereIn('permission_id', $permissionIds)->delete();
            DB::table('model_has_permissions')->whereIn('permission_id', $permissionIds)->delete();
            DB::table('permissions')->whereIn('id', $permissionIds)->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Permissions are re-seeded via PermissionSeeder and RoleSeeder
    }
};
