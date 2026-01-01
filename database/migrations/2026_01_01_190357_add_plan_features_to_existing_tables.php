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
        // Add pov_type to scenes table
        Schema::table('scenes', function (Blueprint $table) {
            $table->string('pov_type', 30)->nullable()->after('pov_character_id');
        });

        // Add preset_type to scene_labels table
        Schema::table('scene_labels', function (Blueprint $table) {
            $table->string('preset_type', 50)->nullable()->after('color');
        });

        // Add summary and disable_numeration to chapters table
        Schema::table('chapters', function (Blueprint $table) {
            $table->text('summary')->nullable()->after('title');
            $table->boolean('disable_numeration')->default(false)->after('settings');
        });

        // Add disable_numeration to acts table
        Schema::table('acts', function (Blueprint $table) {
            $table->boolean('disable_numeration')->default(false)->after('position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scenes', function (Blueprint $table) {
            $table->dropColumn('pov_type');
        });

        Schema::table('scene_labels', function (Blueprint $table) {
            $table->dropColumn('preset_type');
        });

        Schema::table('chapters', function (Blueprint $table) {
            $table->dropColumn(['summary', 'disable_numeration']);
        });

        Schema::table('acts', function (Blueprint $table) {
            $table->dropColumn('disable_numeration');
        });
    }
};
