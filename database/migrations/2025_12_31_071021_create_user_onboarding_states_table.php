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
        Schema::create('user_onboarding_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('welcome_completed')->default(false);
            $table->boolean('first_novel_created')->default(false);
            $table->boolean('editor_toured')->default(false);
            $table->boolean('codex_introduced')->default(false);
            $table->boolean('ai_chat_introduced')->default(false);
            $table->boolean('onboarding_skipped')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_onboarding_states');
    }
};
