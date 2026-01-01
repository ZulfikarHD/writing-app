<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sprint 16: US-12.13 - Categories with Tag Integration
 *
 * Adds the ability for categories to be linked to specific tags or detail values.
 * Entries with the linked tag/detail automatically appear in the category.
 *
 * Reference: NovelCrafter Codex Categories
 *
 * @see https://www.novelcrafter.com/help/docs/codex/codex-categories
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('codex_categories', function (Blueprint $table) {
            // Link category to a tag - entries with this tag auto-appear in category
            $table->foreignId('linked_tag_id')
                ->nullable()
                ->after('sort_order')
                ->constrained('codex_tags')
                ->nullOnDelete();

            // Link category to a detail definition (dropdown type)
            $table->foreignId('linked_detail_definition_id')
                ->nullable()
                ->after('linked_tag_id')
                ->constrained('codex_detail_definitions')
                ->nullOnDelete();

            // The specific dropdown value to match
            $table->string('linked_detail_value')
                ->nullable()
                ->after('linked_detail_definition_id');
        });
    }

    public function down(): void
    {
        Schema::table('codex_categories', function (Blueprint $table) {
            $table->dropForeign(['linked_tag_id']);
            $table->dropForeign(['linked_detail_definition_id']);
            $table->dropColumn(['linked_tag_id', 'linked_detail_definition_id', 'linked_detail_value']);
        });
    }
};
