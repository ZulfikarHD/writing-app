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
        // Series-level codex entries (shared across all novels in a series)
        Schema::create('series_codex_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('series_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // character, location, item, lore, organization, subplot
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->string('ai_context_mode')->default('detected'); // always, detected, manual, never
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['series_id', 'type']);
            $table->index(['series_id', 'is_archived']);
        });

        // Series codex aliases (names/nicknames for series entries)
        Schema::create('series_codex_aliases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('series_codex_entry_id')->constrained()->cascadeOnDelete();
            $table->string('alias');
            $table->timestamps();

            $table->index('alias');
        });

        // Series codex details (custom key-value attributes)
        Schema::create('series_codex_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('series_codex_entry_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->text('value');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Link table to track which series entries are inherited by novels
        // (allows novels to override/exclude specific series entries)
        Schema::create('novel_series_codex_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('novel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('series_codex_entry_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_excluded')->default(false); // Exclude from this novel
            $table->foreignId('local_entry_id')->nullable()->constrained('codex_entries')->nullOnDelete(); // Local override
            $table->timestamps();

            $table->unique(['novel_id', 'series_codex_entry_id'], 'novel_series_codex_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('novel_series_codex_overrides');
        Schema::dropIfExists('series_codex_details');
        Schema::dropIfExists('series_codex_aliases');
        Schema::dropIfExists('series_codex_entries');
    }
};
