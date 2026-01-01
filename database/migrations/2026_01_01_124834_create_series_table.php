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
        Schema::create('series', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('cover_path')->nullable();
            $table->string('genre')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'sort_order']);
        });

        // Add series_id to novels table
        Schema::table('novels', function (Blueprint $table) {
            $table->foreignId('series_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->unsignedInteger('series_order')->nullable()->after('series_id');

            $table->index(['series_id', 'series_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('novels', function (Blueprint $table) {
            $table->dropForeign(['series_id']);
            $table->dropIndex(['series_id', 'series_order']);
            $table->dropColumn(['series_id', 'series_order']);
        });

        Schema::dropIfExists('series');
    }
};
