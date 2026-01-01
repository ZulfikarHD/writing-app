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
        Schema::create('codex_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_entry_id')->constrained('codex_entries')->cascadeOnDelete();
            $table->foreignId('target_entry_id')->constrained('codex_entries')->cascadeOnDelete();
            $table->string('relation_type', 100);
            $table->string('label')->nullable();
            $table->boolean('is_bidirectional')->default(false);
            $table->timestamps();

            $table->index('source_entry_id');
            $table->index('target_entry_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('codex_relations');
    }
};
