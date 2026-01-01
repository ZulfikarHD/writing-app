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
        Schema::create('codex_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('codex_entry_id')->constrained()->cascadeOnDelete();
            $table->string('key_name');
            $table->text('value');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('codex_entry_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('codex_details');
    }
};
