<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chat_threads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('novel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('model')->nullable(); // Per-thread model override
            $table->foreignId('connection_id')->nullable()->constrained('ai_connections')->nullOnDelete();
            $table->json('context_settings')->nullable(); // Context preferences
            $table->boolean('is_pinned')->default(false);
            $table->foreignId('linked_scene_id')->nullable()->constrained('scenes')->nullOnDelete();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index(['novel_id', 'user_id']);
            $table->index(['novel_id', 'archived_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_threads');
    }
};
