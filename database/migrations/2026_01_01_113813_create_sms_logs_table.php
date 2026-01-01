<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->string('to');
            $table->text('message');
            $table->string('type')->default('notification'); // otp, notification, custom
            $table->string('status')->default('sent'); // sent, failed
            $table->string('provider')->nullable(); // local_syria, twilio, etc.
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['to', 'sent_at']);
            $table->index(['status', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};
