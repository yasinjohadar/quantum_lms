<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Remove Zoom integration: drop zoom tables, zoom_meeting_id from attendance_logs, and zoom system settings.
     */
    public function up(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropColumn('zoom_meeting_id');
        });

        Schema::dropIfExists('zoom_join_tokens');
        Schema::dropIfExists('zoom_meetings');
        Schema::dropIfExists('zoom_accounts');

        DB::table('system_settings')->where('group', 'zoom')->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('zoom_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('account_id')->nullable();
            $table->string('client_id')->nullable();
            $table->text('client_secret')->nullable();
            $table->string('sdk_key')->nullable();
            $table->text('sdk_secret')->nullable();
            $table->string('redirect_uri')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('zoom_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_session_id')->constrained('live_sessions')->onDelete('cascade');
            $table->string('zoom_meeting_id');
            $table->string('zoom_uuid')->unique();
            $table->string('host_email')->nullable();
            $table->string('host_id')->nullable();
            $table->string('topic')->nullable();
            $table->timestampTz('start_time');
            $table->integer('duration');
            $table->string('timezone')->nullable();
            $table->text('encrypted_passcode')->nullable();
            $table->string('status')->default('created');
            $table->json('settings_json')->nullable();
            $table->timestampsTz();
            $table->index('zoom_meeting_id');
        });

        Schema::create('zoom_join_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_session_id')->constrained('live_sessions')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('token', 64)->unique();
            $table->timestampTz('expires_at');
            $table->integer('use_count')->default(0);
            $table->integer('max_uses')->default(1);
            $table->string('user_agent_hash', 64)->nullable();
            $table->string('ip_prefix', 45)->nullable();
            $table->timestampsTz();
            $table->index(['live_session_id', 'user_id']);
        });

        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->string('zoom_meeting_id')->nullable()->after('live_session_id');
        });
    }
};
