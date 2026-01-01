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
        Schema::table('codex_progressions', function (Blueprint $table) {
            // Mode: 'addition' (default) appends info, 'replace' overwrites detail value
            $table->string('mode', 20)->default('addition')->after('new_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('codex_progressions', function (Blueprint $table) {
            $table->dropColumn('mode');
        });
    }
};
