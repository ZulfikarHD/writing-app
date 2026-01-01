<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sprint 13: Auto-Mentions & Research
 *
 * This migration adds:
 * - US-12.2: Tracking toggle per entry (is_tracking_enabled)
 * - US-12.3: Research notes tab (research_notes)
 * - F-12.2.2: External links storage (codex_external_links table)
 *
 * @see https://www.novelcrafter.com/help/docs/codex/anatomy-codex-entry
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // US-12.2 & US-12.3: Add new columns to codex_entries
        Schema::table('codex_entries', function (Blueprint $table) {
            // US-12.2: Tracking toggle - when disabled, entry won't be highlighted
            // or tracked in mention scans, but still available for manual AI context
            $table->boolean('is_tracking_enabled')->default(true)->after('is_archived');

            // US-12.3: Research notes - private notes NOT sent to AI
            // Stores development notes, inspiration, spoilers, etc.
            $table->text('research_notes')->nullable()->after('description');
        });

        // F-12.2.2: External links storage for research purposes
        Schema::create('codex_external_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('codex_entry_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('url', 2048);
            $table->text('notes')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('codex_entry_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('codex_external_links');

        Schema::table('codex_entries', function (Blueprint $table) {
            $table->dropColumn(['is_tracking_enabled', 'research_notes']);
        });
    }
};
