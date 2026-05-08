<?php
// app/Http/Controllers/ChatController.php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Services\AIService;
use App\Services\NLPService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ChatController extends Controller
{
    public function __construct(
        private AIService  $ai,
        private NLPService $nlp,
    ) {}

    /**
     * Show chat page — loads or creates today's conversation for current period.
     */
    public function index()
    {
        $user         = auth()->user();
        $conversation = $user->todaysConversation();
        $messages     = $conversation->messages;
        $period       = Conversation::resolvePeriod();
        $periodMeta   = Conversation::periodLabel($period);

        return view('chat.index', compact('conversation', 'messages', 'period', 'periodMeta'));
    }

    /**
     * Receive a message, persist it, call AI, return reply.
     */
    public function send(Request $request): JsonResponse
    {
        $request->validate(['message' => 'required|string|max:1000']);

        $user         = auth()->user();
        $conversation = $user->todaysConversation();
        $userText     = trim($request->input('message'));

        // Save user message
        $conversation->messages()->create([
            'sender_type' => 'user',
            'content'     => $userText,
        ]);

        // Build AI history
        $history = $conversation->toAIHistory(30);
        array_pop($history);

        // Get AI reply
        $reply = $this->ai->chat($history, $userText);

        // Save AI reply
        $conversation->messages()->create([
            'sender_type' => 'ai',
            'content'     => $reply,
        ]);

        $sentiment = $this->nlp->quickSentiment($userText);

        return response()->json([
            'reply'     => $reply,
            'sentiment' => $sentiment,
            'timestamp' => now()->format('g:i A'),
        ]);
    }
}