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
        Schema::create('ai_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('provider', [
                'openai',
                'anthropic',
                'openrouter',
                'ollama',
                'groq',
                'lm_studio',
                'openai_compatible',
            ]);
            $table->string('name');
            $table->text('api_key_encrypted')->nullable();
            $table->string('base_url', 500)->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamp('last_tested_at')->nullable();
            $table->enum('last_test_status', ['success', 'failed', 'pending'])->default('pending');
            $table->timestamps();

            $table->index(['user_id', 'provider']);
            $table->index(['user_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_connections');
    }
};
