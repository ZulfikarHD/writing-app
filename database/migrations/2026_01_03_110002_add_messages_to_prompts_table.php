<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds support for multiple user/AI messages in prompts.
     * The messages column stores an array of {role: 'user'|'assistant', content: string}
     */
    public function up(): void
    {
        Schema::table('prompts', function (Blueprint $table) {
            $table->json('messages')->nullable()->after('user_message');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prompts', function (Blueprint $table) {
            $table->dropColumn('messages');
        });
    }
};
