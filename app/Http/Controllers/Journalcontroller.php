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
     * List all journal entries for the authenticated user.
     */
    public function index()
    {
        $userId = auth()->id();
 
        $entries = JournalEntry::where('user_id', $userId)
            ->orderBy('entry_date', 'desc')
            ->paginate(10);
 
        $recentMoods = JournalEntry::where('user_id', $userId)
            ->orderBy('entry_date', 'desc')
            ->limit(7)
            ->get(['entry_date', 'mood']);
 
        return view('journal.index', compact('entries', 'recentMoods'));
    }
 
    /**
     * Show a single journal entry.
     */
    public function show(string $date)
    {
        $entry = JournalEntry::where('user_id', auth()->id())
            ->where('entry_date', $date)
            ->firstOrFail();
 
        return view('journal.show', compact('entry'));
    }
 
    /**
     * Compile today's conversation into a journal entry (AJAX).
     *
     * Instead of trusting the date sent from the browser (which can be
     * timezone-mismatched), we find the user's most recent conversation
     * directly and use its date. This guarantees we always compile the
     * conversation the user is actually looking at.
     */
    public function compile(Request $request): JsonResponse
    {
        $userId = auth()->id();
 
        // Find the most recent conversation for this user
        $latestConversation = Conversation::where('user_id', $userId)
            ->latest()
            ->first();
 
        if (!$latestConversation) {
            return response()->json([
                'success' => false,
                'message' => 'No conversations found. Start chatting first!',
            ], 422);
        }
 
        // Use the actual date the conversation was created in the DB
        // This avoids any timezone mismatch between browser and server
        $date = $latestConversation->created_at->toDateString();
 
        $entry = $this->compiler->compileForDate($userId, $date);
 
        if (!$entry) {
            return response()->json([
                'success' => false,
                'message' => 'No messages found to compile.',
            ], 422);
        }
 
        return response()->json([
            'success' => true,
            'entry'   => [
                'summary'    => $entry->summary,
                'mood'       => $entry->mood,
                'mood_emoji' => $entry->mood_emoji,
                'keywords'   => $entry->keywords ?? [],
                'date'       => $entry->entry_date->format('F j, Y'),
            ],
        ]);
    }
}
