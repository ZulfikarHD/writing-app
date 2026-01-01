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
        Schema::create('codex_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('novel_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['character', 'location', 'item', 'lore', 'organization', 'subplot']);
            $table->string('name');
            $table->longText('description')->nullable();
            $table->string('thumbnail_path', 500)->nullable();
            $table->enum('ai_context_mode', ['always', 'detected', 'manual', 'never'])->default('detected');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['novel_id', 'type']);
            $table->index(['novel_id', 'is_archived']);
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('codex_entries');
    }
};
