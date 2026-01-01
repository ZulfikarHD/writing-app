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
        Schema::create('codex_mentions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('codex_entry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('scene_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('mention_count')->default(1);
            $table->json('positions')->nullable();
            $table->timestamps();

            $table->unique(['codex_entry_id', 'scene_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('codex_mentions');
    }
};
