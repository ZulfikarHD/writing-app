<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sprint 14: Tags System & Enhanced Details
 *
 * This migration adds:
 * - US-12.4: Tags System (codex_tags, codex_entry_tags)
 * - US-12.5: Enhanced Detail Types (codex_detail_definitions)
 * - US-12.6: AI Visibility per Detail
 * - US-12.7: Detail Presets
 * - F-12.3.2: Tag Management
 * - F-12.3.4: Predefined Tags per Type
 *
 * @see https://www.novelcrafter.com/help/docs/codex/codex-details
 * @see https://www.novelcrafter.com/help/docs/codex/codex-categories
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // US-12.4: Tags System - Tag definitions per novel
        Schema::create('codex_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('novel_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('color', 7)->nullable(); // Hex color like #FF5733
            $table->string('entry_type')->nullable(); // Limit to specific codex entry types (character, location, etc.)
            $table->boolean('is_predefined')->default(false); // System-defined tags
            $table->timestamps();

            $table->unique(['novel_id', 'name']);
            $table->index(['novel_id', 'entry_type']);
        });

        // US-12.4: Tags System - Pivot table for entry-tag assignments
        Schema::create('codex_entry_tags', function (Blueprint $table) {
            $table->foreignId('codex_entry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('codex_tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['codex_entry_id', 'codex_tag_id']);
        });

        // US-12.5 & US-12.7: Detail Definitions (Templates/Presets)
        // Defines the structure and type of details that can be added to entries
        Schema::create('codex_detail_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('novel_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g., "Story Role", "Pronouns", "Backstory"
            $table->string('type'); // text, line, dropdown, codex_reference
            $table->json('options')->nullable(); // For dropdown: array of option values
            $table->json('entry_types')->nullable(); // Which codex types can use this (null = all)
            $table->boolean('show_in_sidebar')->default(false); // Display value next to entry name
            $table->string('ai_visibility')->default('always'); // always, never, nsfw_only
            $table->integer('sort_order')->default(0);
            $table->boolean('is_preset')->default(false); // System-provided preset
            $table->timestamps();

            $table->index('novel_id');
            $table->index('is_preset');
        });

        // US-12.5 & US-12.6: Update codex_details with definition reference and AI visibility
        Schema::table('codex_details', function (Blueprint $table) {
            // Reference to detail definition (for typed details)
            $table->foreignId('definition_id')
                ->nullable()
                ->after('codex_entry_id')
                ->constrained('codex_detail_definitions')
                ->nullOnDelete();

            // US-12.6: AI Visibility per Detail - controls when this detail is sent to AI
            // always = always included, never = never included, nsfw_only = only for NSFW prompts
            $table->string('ai_visibility')->default('always')->after('sort_order');

            // Detail type for non-definition details (backwards compatibility)
            $table->string('type')->default('text')->after('ai_visibility');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('codex_details', function (Blueprint $table) {
            $table->dropForeign(['definition_id']);
            $table->dropColumn(['definition_id', 'ai_visibility', 'type']);
        });

        Schema::dropIfExists('codex_detail_definitions');
        Schema::dropIfExists('codex_entry_tags');
        Schema::dropIfExists('codex_tags');
    }
};
