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
     * Compile a journal entry for a given user + date.
     * Pulls messages from ALL conversations the user had that day.
     * Upserts the journal_entries row (safe to call multiple times).
     */
    public function compileForDate(int $userId, string $date): ?JournalEntry
    {
        // Gather all conversations started on this date
        $conversations = Conversation::where('user_id', $userId)
            ->whereDate('created_at', $date)
            ->with('messages')
            ->get();

        if ($conversations->isEmpty()) {
            return null;
        }

        // Flatten all messages across all conversations for this day
        $allMessages = $conversations
            ->flatMap(fn($c) => $c->messages->toArray())
            ->toArray();

        $userMessages = collect($allMessages)->where('sender_type', 'user');

        if ($userMessages->isEmpty()) {
            return null;
        }

        // NLP analysis — returns only 'mood' and 'keywords'
        $nlpData = $this->nlp->analyseConversation($allMessages);

        // AI summary
        $summary = $this->ai->compileSummary($allMessages, $nlpData);

        // Upsert — matches journal_entries schema exactly
        $entry = JournalEntry::updateOrCreate(
            [
                'user_id'    => $userId,
                'entry_date' => $date,
            ],
            [
                'summary'  => $summary,
                'mood'     => $nlpData['mood'],
                'keywords' => $nlpData['keywords'],
            ]
        );

        Log::info("Journal compiled", [
            'user_id' => $userId,
            'date'    => $date,
            'mood'    => $nlpData['mood'],
        ]);

        return $entry;
    }
}
