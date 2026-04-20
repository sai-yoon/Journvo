<?php
// app/Services/NLPService.php

namespace App\Services;

class NLPService
{
    // ─── Sentiment Word Lists ─────────────────────────────────────────────────

    private array $positiveWords = [
        'happy'       => 3, 'excited'     => 3, 'amazing'     => 3, 'wonderful'   => 3,
        'great'       => 2, 'good'        => 2, 'fantastic'   => 3, 'excellent'   => 3,
        'joyful'      => 3, 'thrilled'    => 3, 'elated'      => 3, 'grateful'    => 2,
        'blessed'     => 2, 'relaxed'     => 2, 'calm'        => 1, 'peaceful'    => 2,
        'proud'       => 2, 'confident'   => 2, 'energized'   => 2, 'motivated'   => 2,
        'accomplished'=> 3, 'succeeded'   => 3, 'won'         => 2, 'fun'         => 2,
        'enjoyed'     => 2, 'love'        => 2, 'loved'       => 2, 'awesome'     => 3,
        'better'      => 1, 'nice'        => 1, 'laugh'       => 2, 'smiled'      => 2,
        'smile'       => 2, 'cheerful'    => 2, 'refreshed'   => 2, 'productive'  => 2,
        'hopeful'     => 2, 'optimistic'  => 2, 'satisfied'   => 2, 'content'     => 1,
    ];

    private array $negativeWords = [
        'tired'       => -2, 'exhausted'  => -3, 'stressed'   => -3, 'anxious'    => -2,
        'worried'     => -2, 'sad'        => -2, 'depressed'  => -3, 'miserable'  => -3,
        'angry'       => -2, 'frustrated' => -2, 'annoyed'    => -2, 'upset'      => -2,
        'overwhelmed' => -3, 'terrible'   => -3, 'awful'      => -3, 'horrible'   => -3,
        'bad'         => -2, 'failed'     => -2, 'failure'    => -3, 'disappointed'=> -2,
        'bored'       => -1, 'lonely'     => -2, 'lost'       => -1, 'confused'   => -1,
        'difficult'   => -1, 'hard'       => -1, 'struggle'   => -2, 'struggling' => -2,
        'hate'        => -2, 'hated'      => -2, 'sick'       => -2, 'pain'       => -2,
        'hurt'        => -2, 'crying'     => -2, 'cried'      => -2, 'nervous'    => -2,
        'scared'      => -2, 'fear'       => -2, 'embarrassed'=> -1, 'guilty'     => -1,
    ];

    private array $stopWords = [
        'the','a','an','and','or','but','in','on','at','to','for','of','with',
        'is','was','are','were','be','been','being','have','has','had','do','does',
        'did','will','would','shall','should','may','might','can','could','must',
        'i','you','he','she','it','we','they','me','him','her','us','them',
        'my','your','his','its','our','their','this','that','these','those',
        'what','which','who','when','where','how','why','not','no','so','just',
        'like','very','really','quite','more','most','also','too','then','than',
        'up','down','out','about','over','after','before','all','some','any',
        'because','if','as','by','from','into','through','during','while',
    ];

    // ─── Public API ───────────────────────────────────────────────────────────

    /**
     * Analyse a conversation's messages.
     * Returns only fields that exist in the journal_entries schema.
     */
    public function analyseConversation(array $messages): array
    {
        $userText = $this->extractUserText($messages);
        $mood     = $this->detectMood($userText);
        $keywords = $this->extractKeywords($userText, 8);

        return [
            'mood'     => $mood,
            'keywords' => $keywords,
        ];
    }

    /**
     * Quick single-message sentiment for real-time UI feedback.
     */
    public function quickSentiment(string $text): string
    {
        return $this->detectMood($text);
    }

    // ─── Internal ─────────────────────────────────────────────────────────────

    private function extractUserText(array $messages): string
    {
        return collect($messages)
            ->where('sender_type', 'user')
            ->pluck('content')
            ->join(' ');
    }

    private function detectMood(string $text): string
    {
        $text  = strtolower($text);
        $words = preg_split('/\W+/', $text, -1, PREG_SPLIT_NO_EMPTY);

        $score = 0;
        $hits  = 0;

        foreach ($words as $word) {
            if (isset($this->positiveWords[$word])) {
                $score += $this->positiveWords[$word];
                $hits++;
            } elseif (isset($this->negativeWords[$word])) {
                $score += $this->negativeWords[$word];
                $hits++;
            }
        }

        if ($hits === 0) return 'neutral';

        $avg = $score / $hits;
        if ($avg >= 1.0)  return 'positive';
        if ($avg <= -1.0) return 'negative';
        return 'neutral';
    }

    public function extractKeywords(string $text, int $limit = 8): array
    {
        $words = preg_split('/\W+/', strtolower($text), -1, PREG_SPLIT_NO_EMPTY);

        $freq = [];
        foreach ($words as $word) {
            if (strlen($word) < 3)                continue;
            if (in_array($word, $this->stopWords)) continue;
            $freq[$word] = ($freq[$word] ?? 0) + 1;
        }

        // Boost emotional words so they surface in keywords
        foreach ($freq as $word => &$count) {
            if (isset($this->positiveWords[$word]) || isset($this->negativeWords[$word])) {
                $count *= 2;
            }
        }

        arsort($freq);
        return array_keys(array_slice($freq, 0, $limit));
    }
}
