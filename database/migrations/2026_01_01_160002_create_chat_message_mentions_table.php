<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sprint 16: F-12.1.5 - Mention Tracking in Chat
 *
 * Creates a pivot table to track codex entry mentions in chat messages.
 * This migration will only run when the chat_messages table exists.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Only create if chat_messages table exists
        if (! Schema::hasTable('chat_messages')) {
            return;
        }

        Schema::create('chat_message_mentions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_message_id')
                ->constrained('chat_messages')
                ->cascadeOnDelete();
            $table->foreignId('codex_entry_id')
                ->constrained('codex_entries')
                ->cascadeOnDelete();
            $table->integer('mention_count')->default(0);
            $table->timestamps();

            $table->unique(['chat_message_id', 'codex_entry_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_message_mentions');
    }
};
