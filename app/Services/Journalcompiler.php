<?php
// app/Services/JournalCompiler.php

namespace App\Services;

use App\Models\Conversation;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\Log;

class JournalCompiler
{
    public function __construct(
        private NLPService $nlp,
        private AIService  $ai,
    ) {}

    /**
     * Compile a period summary (morning/noon/evening) for a given date.
     * Also recompiles the overall summary from all period summaries.
     */
    public function compileForDate(int $userId, string $date): ?JournalEntry
    {
        // Find the most recent conversation for this user on this date
        $latestConversation = Conversation::where('user_id', $userId)
            ->whereDate('created_at', $date)
            ->latest()
            ->first();

        $period = $latestConversation?->time_of_day ?? Conversation::resolvePeriod();

        // Compile the period entry
        $periodEntry = $this->compilePeriod($userId, $date, $period);

        if (!$periodEntry) {
            return null;
        }

        // Recompile the overall entry from all period summaries
        $this->compileOverall($userId, $date);

        return $periodEntry;
    }

    /**
     * Compile a single time-period journal entry.
     */
    public function compilePeriod(int $userId, string $date, string $period): ?JournalEntry
    {
        // Get all conversations for this user + date + period
        $conversations = Conversation::where('user_id', $userId)
            ->whereDate('created_at', $date)
            ->where('time_of_day', $period)
            ->with('messages')
            ->get();

        if ($conversations->isEmpty()) {
            return null;
        }

        $allMessages = $conversations
            ->flatMap(fn($c) => $c->messages->toArray())
            ->toArray();

        $userMessages = collect($allMessages)->where('sender_type', 'user');

        if ($userMessages->isEmpty()) {
            return null;
        }

        $nlpData = $this->nlp->analyseConversation($allMessages);
        $summary = $this->ai->compilePeriodSummary($allMessages, $nlpData, $period);

        $entry = JournalEntry::updateOrCreate(
            [
                'user_id'     => $userId,
                'entry_date'  => $date,
                'time_of_day' => $period,
            ],
            [
                'summary'  => $summary,
                'mood'     => $nlpData['mood'],
                'keywords' => $nlpData['keywords'],
            ]
        );

        Log::info("Period compiled [{$period}]", [
            'user_id' => $userId,
            'date'    => $date,
            'mood'    => $nlpData['mood'],
        ]);

        return $entry;
    }

    /**
     * Compile the overall daily summary from all existing period entries.
     * Uses the period summaries as input — no need to re-read all messages.
     */
    public function compileOverall(int $userId, string $date): ?JournalEntry
    {
        $periodEntries = JournalEntry::where('user_id', $userId)
            ->where('entry_date', $date)
            ->whereIn('time_of_day', ['morning', 'noon', 'evening'])
            ->get();

        if ($periodEntries->isEmpty()) {
            return null;
        }

        // Combine all period summaries into one block for the AI
        $combinedSummaries = $periodEntries->map(function ($e) {
            $meta = $e->period_meta;
            return "{$meta['emoji']} {$meta['label']}: {$e->summary}";
        })->join("\n\n");

        // Determine overall mood by majority
        $moodCounts = $periodEntries->groupBy('mood')->map->count();
        $overallMood = $moodCounts->sortDesc()->keys()->first() ?? 'neutral';

        // Merge all keywords, deduplicate, take top 8
        $allKeywords = $periodEntries
            ->flatMap(fn($e) => $e->keywords ?? [])
            ->unique()
            ->values()
            ->take(8)
            ->toArray();

        $summary = $this->ai->compileOverallSummary($combinedSummaries, $overallMood);

        $entry = JournalEntry::updateOrCreate(
            [
                'user_id'     => $userId,
                'entry_date'  => $date,
                'time_of_day' => 'overall',
            ],
            [
                'summary'  => $summary,
                'mood'     => $overallMood,
                'keywords' => $allKeywords,
            ]
        );

        Log::info("Overall compiled", [
            'user_id' => $userId,
            'date'    => $date,
            'mood'    => $overallMood,
        ]);

        return $entry;
    }
}