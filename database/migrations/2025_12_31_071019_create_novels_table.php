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
        Schema::create('novels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pen_name_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('genre')->nullable();
            $table->string('pov')->nullable(); // first_person, third_person_limited, third_person_omniscient
            $table->string('tense')->nullable(); // past, present
            $table->string('cover_path')->nullable();
            $table->unsignedInteger('word_count')->default(0);
            $table->unsignedInteger('chapter_count')->default(0);
            $table->string('status')->default('draft'); // draft, in_progress, completed, archived
            $table->timestamp('last_edited_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('last_edited_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('novels');
    }
};
