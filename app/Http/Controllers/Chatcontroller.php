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
     * Show chat page — loads or creates today's conversation.
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user         = auth()->user();
        $conversation = $user->todaysConversation();
        $messages     = $conversation->messages;

        return view('chat.index', compact('conversation', 'messages'));
    }

    /**
     * Receive a message, persist it, call AI, return reply.
     */
    public function send(Request $request): JsonResponse
    {
        $request->validate(['message' => 'required|string|max:1000']);

        /** @var \App\Models\User $user */
        $user         = auth()->user();
        $conversation = $user->todaysConversation();
        $userText     = trim($request->input('message'));

        // 1. Save user message
        $conversation->messages()->create([
            'sender_type' => 'user',
            'content'     => $userText,
        ]);

        // 2. Build AI history (maps sender_type → role for OpenRouter)
        $history = $conversation->toAIHistory(30);
        // Remove the message we just added — it will be appended by AIService
        array_pop($history);

        // 3. Get AI reply
        $reply = $this->ai->chat($history, $userText);

        // 4. Save AI reply
        $conversation->messages()->create([
            'sender_type' => 'ai',
            'content'     => $reply,
        ]);

        // 5. Quick sentiment for real-time mood dot
        $sentiment = $this->nlp->quickSentiment($userText);

        return response()->json([
            'reply'     => $reply,
            'sentiment' => $sentiment,
            'timestamp' => now()->format('g:i A'),
        ]);
    }
}
