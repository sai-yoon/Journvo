<?php
// app/Services/AIService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    private string $apiKey;
    private string $baseUrl;

    private array $modelFallbacks = [
        'openrouter/auto',
        'google/gemini-2.0-flash-exp:free',
        'deepseek/deepseek-r1:free',
        'meta-llama/llama-4-scout:free',
        'qwen/qwen3-8b:free',
    ];

    public function __construct()
    {
        $this->apiKey  = config('services.openrouter.key');
        $this->baseUrl = config('services.openrouter.url', 'https://openrouter.ai/api/v1');

        $envModel = config('services.openrouter.model', 'openrouter/auto');
        $this->modelFallbacks = array_unique(
            array_merge([$envModel], $this->modelFallbacks)
        );
    }

    /**
     * Send a message and get a memory-aware, emotion-aware reply.
     *
     * @param array  $history     Full conversation history for today
     * @param string $userMessage The latest message from the user
     */
    public function chat(array $history, string $userMessage): string
    {
        // Extract memory context from the conversation history
        $memoryContext  = $this->buildMemoryContext($history);

        // Detect the emotion in the current message
        $emotion        = $this->detectEmotion($userMessage);

        // Build the enriched system prompt
        $systemPrompt   = $this->systemPrompt($memoryContext, $emotion);

        $messages = array_merge(
            [['role' => 'system', 'content' => $systemPrompt]],
            $history,
            [['role' => 'user', 'content' => $userMessage]]
        );

        $reply = $this->callWithFallback($messages, 300, 0.8);

        return $reply ?? $this->fallbackReply($userMessage, $emotion);
    }

    /**
     * Compile a summary from conversation messages + NLP data.
     */
    public function compileSummary(array $messages, array $nlpData): string
    {
        $conversation = collect($messages)
            ->map(fn($m) => ucfirst($m['sender_type']) . ': ' . $m['content'])
            ->join("\n");

        $mood     = $nlpData['mood'] ?? 'neutral';
        $keywords = implode(', ', $nlpData['keywords'] ?? []);

        $prompt = <<<PROMPT
You are a thoughtful journal assistant. Write a concise 2–3 sentence daily journal summary
in third person, based on this conversation.

Detected mood: {$mood}
Key themes: {$keywords}

Conversation:
{$conversation}

Write only the summary paragraph. No title, no preamble.
PROMPT;

        $reply = $this->callWithFallback(
            [['role' => 'user', 'content' => $prompt]],
            200,
            0.6
        );

        if ($reply) return $reply;

        // NLP-only fallback if ALL models fail
        $moodPhrases = [
            'positive' => 'had an uplifting and positive day',
            'negative' => 'faced some challenges and difficult emotions today',
            'neutral'  => 'had a reflective and mixed day',
        ];

        $summary = 'Today, the user ' . ($moodPhrases[$mood] ?? 'had a notable day') . '.';
        if (!empty($nlpData['keywords'])) {
            $summary .= ' Themes that came up: ' . implode(', ', array_slice($nlpData['keywords'], 0, 5)) . '.';
        }

        return $summary;
    }

    // ─── Memory Context Builder ───────────────────────────────────────────────

    /**
     * Scan conversation history and extract meaningful context the AI
     * should "remember" — topics, emotions, and things the user mentioned.
     *
     * This turns raw history into a short briefing paragraph injected
     * into the system prompt so every reply feels aware of what was said.
     */
    private function buildMemoryContext(array $history): string
    {
        if (empty($history)) {
            return '';
        }

        // Only look at user messages
        $userMessages = collect($history)
            ->filter(fn($m) => ($m['role'] ?? '') === 'user')
            ->pluck('content')
            ->toArray();

        if (empty($userMessages)) {
            return '';
        }

        $memories = [];

        // ── 1. Topics & keywords mentioned ───────────────────────────────────
        $topicPatterns = [
            'work'      => ['work', 'job', 'office', 'boss', 'meeting', 'deadline', 'colleague', 'project', 'client'],
            'school'    => ['school', 'class', 'exam', 'study', 'professor', 'assignment', 'grade', 'university', 'college'],
            'family'    => ['family', 'mom', 'dad', 'mother', 'father', 'sister', 'brother', 'parent', 'kids', 'children'],
            'health'    => ['sick', 'tired', 'exhausted', 'sleep', 'headache', 'pain', 'doctor', 'exercise', 'gym'],
            'social'    => ['friend', 'friends', 'date', 'party', 'hangout', 'met', 'talked', 'called'],
            'stress'    => ['stressed', 'stress', 'anxious', 'anxiety', 'overwhelmed', 'pressure', 'worried'],
            'happiness' => ['happy', 'excited', 'great', 'wonderful', 'amazing', 'fantastic', 'proud', 'accomplished'],
        ];

        $mentionedTopics = [];
        $allText = strtolower(implode(' ', $userMessages));

        foreach ($topicPatterns as $topic => $words) {
            foreach ($words as $word) {
                if (str_contains($allText, $word)) {
                    $mentionedTopics[] = $topic;
                    break;
                }
            }
        }

        if (!empty($mentionedTopics)) {
            $memories[] = 'Topics mentioned so far: ' . implode(', ', array_unique($mentionedTopics)) . '.';
        }

        // ── 2. Specific things the user said they did ─────────────────────────
        $actionVerbs = ['went', 'had', 'did', 'made', 'started', 'finished', 'tried', 'worked', 'studied', 'met', 'talked', 'felt', 'got', 'saw', 'watched'];
        $sharedEvents = [];

        foreach ($userMessages as $msg) {
            $sentences = preg_split('/[.!?]+/', $msg, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($sentences as $sentence) {
                $sentence = trim($sentence);
                if (strlen($sentence) < 10 || strlen($sentence) > 120) continue;
                $words = explode(' ', strtolower($sentence));
                foreach ($actionVerbs as $verb) {
                    if (in_array($verb, $words)) {
                        $sharedEvents[] = ucfirst(strtolower($sentence));
                        break;
                    }
                }
            }
        }

        // Keep only the 3 most recent events to avoid bloating the prompt
        $sharedEvents = array_unique($sharedEvents);
        $sharedEvents = array_slice($sharedEvents, -3);

        if (!empty($sharedEvents)) {
            $memories[] = 'Things the user mentioned: "' . implode('" · "', $sharedEvents) . '".';
        }

        // ── 3. Emotional arc — how the conversation has felt overall ──────────
        $emotionCounts = ['positive' => 0, 'negative' => 0];
        foreach ($userMessages as $msg) {
            $e = $this->detectEmotion($msg);
            if ($e === 'positive') $emotionCounts['positive']++;
            if ($e === 'negative') $emotionCounts['negative']++;
        }

        if ($emotionCounts['positive'] > $emotionCounts['negative'] && $emotionCounts['positive'] > 0) {
            $memories[] = 'The conversation has had a generally positive tone so far.';
        } elseif ($emotionCounts['negative'] > $emotionCounts['positive'] && $emotionCounts['negative'] > 0) {
            $memories[] = 'The user has expressed some difficult feelings during this conversation.';
        }

        if (empty($memories)) {
            return '';
        }

        return "CONVERSATION MEMORY (use this to give contextual, personal responses):\n"
            . implode("\n", $memories);
    }

    // ─── Emotion Detection ────────────────────────────────────────────────────

    /**
     * Detect the dominant emotion in a piece of text.
     * Returns: 'positive' | 'negative' | 'neutral'
     */
    private function detectEmotion(string $text): string
    {
        $text = strtolower($text);

        $positiveWords = [
            'happy', 'excited', 'great', 'amazing', 'wonderful', 'fantastic',
            'good', 'excellent', 'love', 'loved', 'awesome', 'fun', 'enjoy',
            'enjoyed', 'proud', 'glad', 'thrilled', 'motivated', 'accomplished',
            'succeeded', 'relaxed', 'peaceful', 'grateful', 'blessed', 'hopeful',
            'optimistic', 'confident', 'energized', 'productive', 'refreshed',
        ];

        $negativeWords = [
            'tired', 'exhausted', 'stressed', 'anxious', 'worried', 'sad',
            'depressed', 'angry', 'frustrated', 'annoyed', 'upset', 'overwhelmed',
            'terrible', 'awful', 'horrible', 'bad', 'failed', 'failure',
            'disappointed', 'bored', 'lonely', 'lost', 'confused', 'scared',
            'nervous', 'hate', 'hated', 'sick', 'pain', 'hurt', 'crying',
            'struggle', 'struggling', 'difficult', 'hard', 'miserable', 'guilty',
        ];

        $posScore = 0;
        $negScore = 0;
        $words    = preg_split('/\W+/', $text, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($words as $word) {
            if (in_array($word, $positiveWords)) $posScore++;
            if (in_array($word, $negativeWords)) $negScore++;
        }

        if ($posScore > $negScore) return 'positive';
        if ($negScore > $posScore) return 'negative';
        return 'neutral';
    }

    // ─── System Prompt ────────────────────────────────────────────────────────

    /**
     * Build the system prompt, enriched with memory context and
     * emotion-specific guidance so replies feel personal and aware.
     */
    private function systemPrompt(string $memoryContext = '', string $currentEmotion = 'neutral'): string
    {
        $today = now()->format('l, F j, Y');

        // Emotion-specific instruction injected into the prompt
        $emotionGuidance = match($currentEmotion) {
            'positive' => <<<GUIDANCE
            The user's latest message has a POSITIVE tone.
            - Celebrate with them warmly but briefly
            - Ask what specifically made it good or special
            - Example follow-ups: "That's great to hear! What made it so special?", "It sounds like things went well — what was the highlight?", "That must feel really good. What do you think made the difference?"
            GUIDANCE,

            'negative' => <<<GUIDANCE
            The user's latest message has a NEGATIVE or difficult tone.
            - Acknowledge their feelings with empathy first, before asking anything
            - Be gentle — do not minimize or rush to fix
            - Example follow-ups: "That sounds really tough. What's been weighing on you most?", "I'm sorry to hear that. Do you want to talk about what happened?", "That sounds exhausting. How are you holding up?"
            GUIDANCE,

            default => <<<GUIDANCE
            The user's latest message has a NEUTRAL tone.
            - Be curious and gently encouraging
            - Ask open-ended questions to draw out more reflection
            - Example follow-ups: "How did that make you feel?", "What stood out most about that?", "Was that what you expected?"
            GUIDANCE,
        };

        // Memory section — only included if there's something to remember
        $memorySection = !empty($memoryContext)
            ? "\n\n{$memoryContext}\n\nUse the memory above to make your responses feel personal and continuous. Reference what they mentioned naturally — like a friend who was listening, not like a bot reading a list."
            : '';

        return <<<PROMPT
You are Journvo, a warm and empathetic AI journal companion. Today is {$today}.

Your role:
- Help the user reflect on their day through natural conversation
- Ask ONE thoughtful follow-up question at a time — never multiple
- Be supportive, curious, and non-judgmental
- Keep responses concise (2–4 sentences max)
- Never give unsolicited advice or suggestions
- Feel like a trusted friend who genuinely remembers what was said
- Reference earlier parts of the conversation naturally when relevant
- NEVER start a response with "Tell me more." — always be specific

EMOTION GUIDANCE FOR THIS RESPONSE:
{$emotionGuidance}{$memorySection}
PROMPT;
    }

    // ─── Fallback Reply (when API fails) ─────────────────────────────────────

    /**
     * Emotion-aware fallback replies used only when the API is unavailable.
     */
    private function fallbackReply(string $userMessage, string $emotion = 'neutral'): string
    {
        $text = strtolower($userMessage);

        // Specific keyword overrides first
        if (str_contains($text, 'deadline') || str_contains($text, 'work'))
            return $emotion === 'negative'
                ? "Work pressure sounds really draining. Was today's deadline stress similar to what you've felt before?"
                : "Work came up — how did things go today?";

        if (str_contains($text, 'study') || str_contains($text, 'exam') || str_contains($text, 'school'))
            return $emotion === 'negative'
                ? "Studying can be so mentally tiring. What subject is giving you the hardest time?"
                : "Sounds like a productive study session! What were you working on?";

        if (str_contains($text, 'friend') || str_contains($text, 'family'))
            return "Time with people we care about always leaves an impression. How did it go?";

        // Emotion-based general fallbacks
        return match($emotion) {
            'positive' => collect([
                "That's really good to hear! What made today feel that way?",
                "It sounds like things went well — what was the highlight?",
                "That must feel great. What do you think made the difference today?",
            ])->random(),

            'negative' => collect([
                "That sounds really tough. What's been weighing on you most?",
                "I'm sorry you're feeling that way. Do you want to talk about what happened?",
                "That sounds exhausting. How are you holding up?",
            ])->random(),

            default => collect([
                "How did that make you feel?",
                "What stood out most about that moment?",
                "Was today what you expected it to be?",
                "What part of your day are you still thinking about?",
            ])->random(),
        };
    }

    // ─── API Caller ───────────────────────────────────────────────────────────

    /**
     * Try each model in the fallback list until one succeeds.
     */
    private function callWithFallback(array $messages, int $maxTokens, float $temperature): ?string
    {
        foreach ($this->modelFallbacks as $model) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => "Bearer {$this->apiKey}",
                    'HTTP-Referer'  => config('app.url'),
                    'X-Title'       => 'Journvo Journal',
                ])->timeout(30)->post("{$this->baseUrl}/chat/completions", [
                    'model'       => $model,
                    'messages'    => $messages,
                    'max_tokens'  => $maxTokens,
                    'temperature' => $temperature,
                ]);

                if ($response->successful()) {
                    $text = trim($response->json('choices.0.message.content', ''));
                    if (!empty($text)) {
                        Log::info("AIService: used model [{$model}]");
                        return $text;
                    }
                }

                Log::warning("AIService: model [{$model}] failed", [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

            } catch (\Exception $e) {
                Log::warning("AIService: model [{$model}] threw exception", [
                    'message' => $e->getMessage(),
                ]);
            }
        }

        Log::error('AIService: all models failed');
        return null;
    }
}