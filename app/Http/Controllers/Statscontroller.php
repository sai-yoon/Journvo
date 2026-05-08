<?php
// app/Http/Controllers/StatsController.php

namespace App\Http\Controllers;

use App\Models\JournalEntry;
use App\Models\Conversation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        $user   = auth()->user();

        // ── Streak calculation ────────────────────────────────────────────
        $currentStreak = $this->calculateCurrentStreak($userId);
        $longestStreak = $this->calculateLongestStreak($userId);

        // ── Overall counts ────────────────────────────────────────────────
        $totalEntries       = JournalEntry::where('user_id', $userId)
                                ->where('time_of_day', 'overall')->count();
        $totalConversations = Conversation::where('user_id', $userId)->count();
        $positiveCount      = JournalEntry::where('user_id', $userId)
                                ->where('time_of_day', 'overall')
                                ->where('mood', 'positive')->count();
        $neutralCount       = JournalEntry::where('user_id', $userId)
                                ->where('time_of_day', 'overall')
                                ->where('mood', 'neutral')->count();
        $negativeCount      = JournalEntry::where('user_id', $userId)
                                ->where('time_of_day', 'overall')
                                ->where('mood', 'negative')->count();

        // ── Mood chart — last 30 days ─────────────────────────────────────
        $moodChart = $this->buildMoodChart($userId, 30);

        // ── Period activity — morning/noon/evening breakdown ──────────────
        $periodActivity = $this->buildPeriodActivity($userId);

        // ── Best journaling day of week ───────────────────────────────────
        $bestDayOfWeek = $this->buildDayOfWeekStats($userId);

        // ── Monthly heatmap — current year ───────────────────────────────
        $heatmap = $this->buildHeatmap($userId);

        // ── Most common keywords (top 10) ────────────────────────────────
        $topKeywords = $this->buildTopKeywords($userId);

        // ── Monthly entry counts (last 6 months) ─────────────────────────
        $monthlyStats = $this->buildMonthlyStats($userId);

        return view('stats.index', compact(
            'user',
            'currentStreak',
            'longestStreak',
            'totalEntries',
            'totalConversations',
            'positiveCount',
            'neutralCount',
            'negativeCount',
            'moodChart',
            'periodActivity',
            'bestDayOfWeek',
            'heatmap',
            'topKeywords',
            'monthlyStats'
        ));
    }

    // ─── Streak Helpers ───────────────────────────────────────────────────────

    private function calculateCurrentStreak(int $userId): int
    {
        $streak = 0;
        $date   = now()->toDateString();

        while (
            JournalEntry::where('user_id', $userId)
                ->where('time_of_day', 'overall')
                ->where('entry_date', $date)
                ->exists()
        ) {
            $streak++;
            $date = Carbon::parse($date)->subDay()->toDateString();
        }

        return $streak;
    }

    private function calculateLongestStreak(int $userId): int
    {
        $dates = JournalEntry::where('user_id', $userId)
            ->where('time_of_day', 'overall')
            ->orderBy('entry_date')
            ->pluck('entry_date')
            ->map(fn($d) => Carbon::parse($d)->toDateString())
            ->unique()
            ->values()
            ->toArray();

        if (empty($dates)) return 0;

        $longest = 1;
        $current = 1;

        for ($i = 1; $i < count($dates); $i++) {
            $prev = Carbon::parse($dates[$i - 1]);
            $curr = Carbon::parse($dates[$i]);

            if ($prev->diffInDays($curr) === 1) {
                $current++;
                $longest = max($longest, $current);
            } else {
                $current = 1;
            }
        }

        return $longest;
    }

    // ─── Chart Data Builders ──────────────────────────────────────────────────

    private function buildMoodChart(int $userId, int $days): array
    {
        $start = now()->subDays($days - 1)->startOfDay();

        $entries = JournalEntry::where('user_id', $userId)
            ->where('time_of_day', 'overall')
            ->where('entry_date', '>=', $start->toDateString())
            ->orderBy('entry_date')
            ->get(['entry_date', 'mood'])
            ->keyBy(fn($e) => $e->entry_date->toDateString());

        $labels = [];
        $values = [];
        $colors = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date      = now()->subDays($i)->toDateString();
            $labels[]  = Carbon::parse($date)->format('M j');
            $entry     = $entries->get($date);
            $mood      = $entry?->mood ?? null;

            // Convert mood to numeric for chart: positive=2, neutral=1, negative=0, null=-1
            $values[]  = match($mood) {
                'positive' => 2,
                'neutral'  => 1,
                'negative' => 0,
                default    => null,
            };

            $colors[]  = match($mood) {
                'positive' => '#7A9E7E',
                'neutral'  => '#8899AA',
                'negative' => '#C47B7B',
                default    => 'transparent',
            };
        }

        return compact('labels', 'values', 'colors');
    }

    private function buildPeriodActivity(int $userId): array
    {
        $counts = JournalEntry::where('user_id', $userId)
            ->whereIn('time_of_day', ['morning', 'noon', 'evening'])
            ->select('time_of_day', DB::raw('count(*) as total'))
            ->groupBy('time_of_day')
            ->pluck('total', 'time_of_day');

        return [
            'morning' => $counts->get('morning', 0),
            'noon'    => $counts->get('noon', 0),
            'evening' => $counts->get('evening', 0),
        ];
    }

    private function buildDayOfWeekStats(int $userId): array
    {
        $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

        $counts = JournalEntry::where('user_id', $userId)
            ->where('time_of_day', 'overall')
            ->select(DB::raw('DAYOFWEEK(entry_date) as dow'), DB::raw('count(*) as total'))
            ->groupBy('dow')
            ->pluck('total', 'dow');

        $result = [];
        foreach ($days as $i => $label) {
            // MySQL DAYOFWEEK: 1=Sun, 2=Mon ... 7=Sat
            $result[] = [
                'day'   => $label,
                'count' => $counts->get($i + 1, 0),
            ];
        }

        return $result;
    }

    private function buildHeatmap(int $userId): array
    {
        $year  = now()->year;
        $start = Carbon::create($year, 1, 1);
        $end   = Carbon::create($year, 12, 31);

        $entries = JournalEntry::where('user_id', $userId)
            ->where('time_of_day', 'overall')
            ->whereYear('entry_date', $year)
            ->select('entry_date', 'mood')
            ->get()
            ->keyBy(fn($e) => $e->entry_date->toDateString());

        $heatmap = [];
        $current = $start->copy();

        while ($current <= $end) {
            $dateStr = $current->toDateString();
            $entry   = $entries->get($dateStr);

            $heatmap[] = [
                'date'  => $dateStr,
                'month' => $current->format('M'),
                'day'   => (int) $current->format('j'),
                'dow'   => (int) $current->format('w'), // 0=Sun
                'week'  => (int) $current->format('W'),
                'mood'  => $entry?->mood ?? null,
            ];

            $current->addDay();
        }

        return $heatmap;
    }

    private function buildTopKeywords(int $userId): array
    {
        $entries = JournalEntry::where('user_id', $userId)
            ->where('time_of_day', 'overall')
            ->whereNotNull('keywords')
            ->pluck('keywords');

        $freq = [];
        foreach ($entries as $keywords) {
            foreach ($keywords as $kw) {
                $kw = strtolower(trim($kw));
                if (strlen($kw) < 3) continue;
                $freq[$kw] = ($freq[$kw] ?? 0) + 1;
            }
        }

        arsort($freq);
        return array_slice($freq, 0, 12, true);
    }

    private function buildMonthlyStats(int $userId): array
    {
        $result = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $count = JournalEntry::where('user_id', $userId)
                ->where('time_of_day', 'overall')
                ->whereYear('entry_date', $month->year)
                ->whereMonth('entry_date', $month->month)
                ->count();

            $result[] = [
                'label' => $month->format('M'),
                'count' => $count,
                'year'  => $month->year,
                'month' => $month->month,
            ];
        }

        return $result;
    }
}