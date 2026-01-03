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
        Schema::create('scene_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scene_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['content', 'note', 'alternative', 'beat'])->default('content');
            $table->string('title')->nullable();
            $table->longText('content')->nullable(); // TipTap JSON content
            $table->string('color', 7)->default('#6366f1'); // Hex color
            $table->boolean('is_collapsed')->default(false);
            $table->boolean('exclude_from_ai')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['scene_id', 'sort_order']);
            $table->index(['scene_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scene_sections');
    }
};
