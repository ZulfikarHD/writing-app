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
        Schema::create('prompt_presets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('prompt_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('model')->nullable();
            $table->decimal('temperature', 3, 2)->default(0.70);
            $table->unsignedInteger('max_tokens')->nullable();
            $table->decimal('top_p', 3, 2)->nullable();
            $table->decimal('frequency_penalty', 3, 2)->nullable();
            $table->decimal('presence_penalty', 3, 2)->nullable();
            $table->json('stop_sequences')->nullable();
            $table->json('input_values')->nullable(); // saved prompt input values
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'prompt_id']);
            $table->index('prompt_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prompt_presets');
    }
};
