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
        Schema::table('scene_sections', function (Blueprint $table) {
            $table->boolean('is_completed')->default(false)->after('exclude_from_ai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scene_sections', function (Blueprint $table) {
            $table->dropColumn('is_completed');
        });
    }
};
