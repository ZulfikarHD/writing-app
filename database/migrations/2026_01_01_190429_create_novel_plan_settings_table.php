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
        Schema::create('novel_plan_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('novel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('current_view', 20)->default('grid'); // grid, matrix, outline
            $table->string('matrix_mode', 20)->default('codex'); // codex, pov, labels, custom, subplot
            $table->string('grid_axis', 20)->default('vertical'); // vertical, horizontal
            $table->string('card_width', 20)->default('medium'); // small, medium, large
            $table->string('card_height', 20)->default('medium'); // full, small, medium, large
            $table->boolean('show_auto_references')->default(true);
            $table->json('custom_matrix_entries')->nullable(); // Array of codex entry IDs for custom mode
            $table->timestamps();

            $table->unique(['novel_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('novel_plan_settings');
    }
};
