<?php
// database/migrations/2024_01_02_000001_add_time_of_day_to_conversations.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->enum('time_of_day', ['morning', 'noon', 'evening'])
                  ->nullable()
                  ->after('user_id');
        });

        Schema::table('journal_entries', function (Blueprint $table) {
            // 'overall' = compiled full-day summary
            // 'morning' | 'noon' | 'evening' = period summaries
            $table->enum('time_of_day', ['overall', 'morning', 'noon', 'evening'])
                  ->default('overall')
                  ->after('entry_date');

            // Drop old unique constraint, replace with one that includes time_of_day
            $table->dropUnique(['user_id', 'entry_date']);
            $table->unique(['user_id', 'entry_date', 'time_of_day']);
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn('time_of_day');
        });

        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'entry_date', 'time_of_day']);
            $table->dropColumn('time_of_day');
            $table->unique(['user_id', 'entry_date']);
        });
    }
};
