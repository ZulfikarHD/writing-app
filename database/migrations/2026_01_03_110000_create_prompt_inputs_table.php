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
        Schema::create('prompt_inputs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prompt_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100); // Variable name used in prompt
            $table->string('label', 255); // Display label
            $table->enum('type', ['text', 'textarea', 'select', 'number', 'checkbox'])->default('text');
            $table->json('options')->nullable(); // For select type - array of {value, label}
            $table->text('default_value')->nullable();
            $table->text('placeholder')->nullable();
            $table->text('description')->nullable(); // Help text
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['prompt_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prompt_inputs');
    }
};
