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
        Schema::create('scenes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->json('content')->nullable();
            $table->text('summary')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->foreignId('pov_character_id')->nullable();
            $table->string('status')->default('draft');
            $table->unsignedInteger('word_count')->default(0);
            $table->string('subtitle')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('exclude_from_ai')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->timestamp('archived_at')->nullable();

            $table->index(['chapter_id', 'position']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scenes');
    }
};
