<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gamification_notifications', function (Blueprint $table) {
            $table->foreignId('actor_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->string('actor_name')->nullable()->after('actor_id');
            $table->string('actor_role')->nullable()->after('actor_name');
            $table->string('action_url', 2048)->nullable()->after('actor_role');
        });
    }

    public function down(): void
    {
        Schema::table('gamification_notifications', function (Blueprint $table) {
            $table->dropForeign(['actor_id']);
            $table->dropColumn(['actor_id', 'actor_name', 'actor_role', 'action_url']);
        });
    }
};
