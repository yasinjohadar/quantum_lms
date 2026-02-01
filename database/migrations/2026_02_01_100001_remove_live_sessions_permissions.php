<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Remove live sessions and attendance permissions from permissions table.
     */
    public function up(): void
    {
        $prefixes = ['live-session-', 'attendance-'];
        foreach ($prefixes as $prefix) {
            $permissionIds = DB::table('permissions')->where('name', 'like', $prefix . '%')->pluck('id');
            if ($permissionIds->isNotEmpty()) {
                DB::table('role_has_permissions')->whereIn('permission_id', $permissionIds)->delete();
                DB::table('model_has_permissions')->whereIn('permission_id', $permissionIds)->delete();
                DB::table('permissions')->whereIn('id', $permissionIds)->delete();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
