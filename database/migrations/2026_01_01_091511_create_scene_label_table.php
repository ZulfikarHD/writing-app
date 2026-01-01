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
        Schema::create('scene_label', function (Blueprint $table) {
            $table->foreignId('scene_id')->constrained()->cascadeOnDelete();
            $table->foreignId('scene_label_id')->constrained()->cascadeOnDelete();

            $table->primary(['scene_id', 'scene_label_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scene_label');
    }
};
