<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sprint 16: F-12.1.4 - Mention Tracking in Summaries
 *
 * Adds source tracking to mentions so we know if a mention was found in:
 * - 'content' - Scene content (TipTap JSON)
 * - 'summary' - Scene summary
 * - 'both' - Both content and summary
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('codex_mentions', function (Blueprint $table) {
            $table->enum('source', ['content', 'summary', 'both'])
                ->default('content')
                ->after('positions');
        });
    }

    public function down(): void
    {
        Schema::table('codex_mentions', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
