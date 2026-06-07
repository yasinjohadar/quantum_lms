<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('section_sync_peers', function (Blueprint $table) {
            $table->id();
            $table->uuid('sync_group_id')->index();
            $table->string('entity_type', 32);
            $table->unsignedBigInteger('canonical_entity_id');
            $table->unsignedBigInteger('peer_entity_id');
            $table->foreignId('target_subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['entity_type', 'canonical_entity_id', 'peer_entity_id'], 'section_sync_peers_unique_pair');
            $table->index(['entity_type', 'peer_entity_id'], 'section_sync_peers_peer_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('section_sync_peers');
    }
};
