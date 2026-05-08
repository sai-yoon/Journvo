<?php
// app/Http/Controllers/JournalController.php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\JournalEntry;
use App\Services\JournalCompiler;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class JournalController extends Controller
{
    public function __construct(private JournalCompiler $compiler) {}

    /**
     * List journal notebooks — one per day, grouped.
     */
    public function index()
    {
        $userId = auth()->id();

        // Get all unique dates that have an 'overall' entry
        $notebooks = JournalEntry::where('user_id', $userId)
            ->where('time_of_day', 'overall')
            ->orderBy('entry_date', 'desc')
            ->paginate(8);

        // For each notebook date, eager-load its period entries
        $dates = $notebooks->pluck('entry_date')->map->toDateString()->toArray();

        $periodEntries = JournalEntry::where('user_id', $userId)
            ->whereIn('time_of_day', ['morning', 'noon', 'evening'])
            ->whereIn('entry_date', $dates)
            ->get()
            ->groupBy(fn($e) => $e->entry_date->toDateString());

        // Last 7 days mood strip
        $recentMoods = JournalEntry::where('user_id', $userId)
            ->where('time_of_day', 'overall')
            ->orderBy('entry_date', 'desc')
            ->limit(7)
            ->get(['entry_date', 'mood']);

        return view('journal.index', compact('notebooks', 'periodEntries', 'recentMoods'));
    }

    /**
     * Show a full notebook for a date (overall + all periods).
     */
    public function show(string $date)
    {
        $userId = auth()->id();

        $overall = JournalEntry::where('user_id', $userId)
            ->where('entry_date', $date)
            ->where('time_of_day', 'overall')
            ->firstOrFail();

        $periods = JournalEntry::where('user_id', $userId)
            ->where('entry_date', $date)
            ->whereIn('time_of_day', ['morning', 'noon', 'evening'])
            ->orderByRaw("FIELD(time_of_day, 'morning', 'noon', 'evening')")
            ->get();

        return view('journal.show', compact('overall', 'periods', 'date'));
    }

    /**
     * Compile current period + overall (AJAX).
     */
    public function compile(Request $request): JsonResponse
    {
        $userId = auth()->id();

        $latestConversation = Conversation::where('user_id', $userId)
            ->latest()
            ->first();

        if (!$latestConversation) {
            return response()->json([
                'success' => false,
                'message' => 'No conversations found. Start chatting first!',
            ], 422);
        }

        $date   = $latestConversation->created_at->toDateString();
        $period = $latestConversation->time_of_day ?? Conversation::resolvePeriod();
        $entry  = $this->compiler->compileForDate($userId, $date);

        if (!$entry) {
            return response()->json([
                'success' => false,
                'message' => 'No messages found to compile.',
            ], 422);
        }

        $meta = Conversation::periodLabel($period);

        return response()->json([
            'success' => true,
            'entry'   => [
                'summary'    => $entry->summary,
                'mood'       => $entry->mood,
                'mood_emoji' => $entry->mood_emoji,
                'period'     => $period,
                'period_label' => $meta['label'],
                'period_emoji' => $meta['emoji'],
                'keywords'   => $entry->keywords ?? [],
                'date'       => $entry->entry_date->format('F j, Y'),
            ],
        ]);
    }
}