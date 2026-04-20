<?php
// app/Console/Commands/CompileJournals.php
// Schedule in app/Console/Kernel.php:
//   $schedule->command('journals:compile')->dailyAt('23:55');

namespace App\Console\Commands;

use App\Models\Conversation;
use App\Services\JournalCompiler;
use Illuminate\Console\Command;

class CompileJournals extends Command
{
    protected $signature   = 'journals:compile {--date= : Specific date Y-m-d, defaults to today}';
    protected $description = 'Compile daily journal entries from conversations';

    public function handle(JournalCompiler $compiler): int
    {
        $date = $this->option('date') ?? now()->toDateString();
        $this->info("Compiling journals for {$date}…");

        // Find all users who had a conversation on this date
        $userIds = Conversation::whereDate('created_at', $date)
            ->distinct()
            ->pluck('user_id');

        if ($userIds->isEmpty()) {
            $this->warn('No conversations found for this date.');
            return self::SUCCESS;
        }

        $compiled = 0;
        foreach ($userIds as $userId) {
            $entry = $compiler->compileForDate($userId, $date);
            if ($entry) {
                $this->line("  ✓ User #{$userId}: {$entry->mood} mood");
                $compiled++;
            }
        }

        $this->info("Done. {$compiled} " . ($compiled === 1 ? 'entry' : 'entries') . " compiled.");
        return self::SUCCESS;
    }
}