{{-- resources/views/stats/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Stats & Insights')

@section('content')
<div class="stats-page">

    {{-- ─── Header ──────────────────────────────────────────────────────── --}}
    <header class="stats-header">
        <div>
            <h1 class="stats-title">Stats & Insights</h1>
            <p class="stats-subtitle">A deeper look at your journaling journey</p>
        </div>
        <div class="stats-header-date">
            <span class="stats-year">{{ now()->format('Y') }}</span>
        </div>
    </header>

    {{-- ─── Hero streak + key numbers ─────────────────────────────────── --}}
    <div class="stats-hero">

        <div class="streak-card">
            <div class="streak-flames">
                @for($i = 0; $i < min($currentStreak, 7); $i++)
                    <span class="streak-flame" style="animation-delay: {{ $i * 0.1 }}s">✦</span>
                @endfor
            </div>
            <div class="streak-number">{{ $currentStreak }}</div>
            <div class="streak-label">day streak</div>
            @if($currentStreak > 0)
                <div class="streak-sub">Keep it going!</div>
            @else
                <div class="streak-sub">Start today ✦</div>
            @endif
        </div>

        <div class="hero-numbers">
            <div class="hero-number-card">
                <span class="hero-number">{{ $totalEntries }}</span>
                <span class="hero-number-label">Total notebooks</span>
            </div>
            <div class="hero-number-card">
                <span class="hero-number">{{ $longestStreak }}</span>
                <span class="hero-number-label">Longest streak</span>
            </div>
            <div class="hero-number-card">
                <span class="hero-number">{{ $totalConversations }}</span>
                <span class="hero-number-label">Conversations</span>
            </div>
            <div class="hero-number-card hero-number-card--positive">
                <span class="hero-number">{{ $totalEntries > 0 ? round(($positiveCount / $totalEntries) * 100) : 0 }}%</span>
                <span class="hero-number-label">Positive days</span>
            </div>
        </div>

    </div>

    {{-- ─── Mood breakdown bar ─────────────────────────────────────────── --}}
    @if($totalEntries > 0)
    <section class="stats-card">
        <h2 class="stats-card-title">
            <span class="stats-card-icon">◈</span> Mood Breakdown
        </h2>
        <div class="mood-breakdown">
            <div class="mood-breakdown-bars">
                @php
                    $total = $positiveCount + $neutralCount + $negativeCount;
                    $posPct = $total > 0 ? round(($positiveCount / $total) * 100) : 0;
                    $neuPct = $total > 0 ? round(($neutralCount  / $total) * 100) : 0;
                    $negPct = $total > 0 ? round(($negativeCount / $total) * 100) : 0;
                @endphp
                <div class="mood-segment mood-segment--positive" style="width: {{ $posPct }}%"
                     title="{{ $positiveCount }} positive days"></div>
                <div class="mood-segment mood-segment--neutral"  style="width: {{ $neuPct }}%"
                     title="{{ $neutralCount }} neutral days"></div>
                <div class="mood-segment mood-segment--negative" style="width: {{ $negPct }}%"
                     title="{{ $negativeCount }} negative days"></div>
            </div>
            <div class="mood-breakdown-legend">
                <div class="mood-legend-item">
                    <span class="mood-legend-dot mood-legend-dot--positive"></span>
                    <span>Positive</span>
                    <strong>{{ $positiveCount }}</strong>
                    <span class="mood-legend-pct">({{ $posPct }}%)</span>
                </div>
                <div class="mood-legend-item">
                    <span class="mood-legend-dot mood-legend-dot--neutral"></span>
                    <span>Neutral</span>
                    <strong>{{ $neutralCount }}</strong>
                    <span class="mood-legend-pct">({{ $neuPct }}%)</span>
                </div>
                <div class="mood-legend-item">
                    <span class="mood-legend-dot mood-legend-dot--negative"></span>
                    <span>Negative</span>
                    <strong>{{ $negativeCount }}</strong>
                    <span class="mood-legend-pct">({{ $negPct }}%)</span>
                </div>
            </div>
        </div>
    </section>
    @endif

    {{-- ─── Mood tracker chart (last 30 days) ─────────────────────────── --}}
    <section class="stats-card">
        <h2 class="stats-card-title">
            <span class="stats-card-icon">◎</span> Mood Tracker
            <span class="stats-card-subtitle">Last 30 days</span>
        </h2>
        <div class="mood-chart-wrap">
            <div class="mood-chart-y">
                <span>😊</span>
                <span>😐</span>
                <span>😔</span>
            </div>
            <div class="mood-chart" id="moodChart">
                @foreach($moodChart['values'] as $i => $val)
                @php $mood = match($val) { 2 => 'positive', 1 => 'neutral', 0 => 'negative', default => 'empty' }; @endphp
                <div class="mood-chart-col" title="{{ $moodChart['labels'][$i] }}{{ $val !== null ? ': ' . $mood : '' }}">
                    <div class="mood-chart-bar mood-chart-bar--{{ $mood }}"
                         style="--mood-val: {{ $val ?? -1 }}">
                    </div>
                    @if($i % 5 === 0)
                        <div class="mood-chart-label">{{ $moodChart['labels'][$i] }}</div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        <div class="mood-chart-legend">
            <span class="mood-chart-legend-item mood-chart-legend-item--positive">😊 Positive</span>
            <span class="mood-chart-legend-item mood-chart-legend-item--neutral">😐 Neutral</span>
            <span class="mood-chart-legend-item mood-chart-legend-item--negative">😔 Negative</span>
            <span class="mood-chart-legend-item mood-chart-legend-item--empty">· No entry</span>
        </div>
    </section>

    {{-- ─── Two-column row: Period activity + Day of week ─────────────── --}}
    <div class="stats-two-col">

        {{-- Period activity --}}
        <section class="stats-card">
            <h2 class="stats-card-title">
                <span class="stats-card-icon">⬡</span> Period Activity
            </h2>
            <div class="period-activity">
                @php
                    $maxPeriod = max($periodActivity['morning'], $periodActivity['noon'], $periodActivity['evening'], 1);
                @endphp
                @foreach([
                    ['morning', '🌅', 'Morning',   $periodActivity['morning']],
                    ['noon',    '☀️',  'Afternoon', $periodActivity['noon']],
                    ['evening', '🌙', 'Evening',   $periodActivity['evening']],
                ] as [$key, $emoji, $label, $count])
                <div class="period-row">
                    <div class="period-row-label">
                        <span>{{ $emoji }}</span>
                        <span>{{ $label }}</span>
                    </div>
                    <div class="period-row-bar-wrap">
                        <div class="period-row-bar period-row-bar--{{ $key }}"
                             style="width: {{ $maxPeriod > 0 ? round(($count / $maxPeriod) * 100) : 0 }}%">
                        </div>
                    </div>
                    <span class="period-row-count">{{ $count }}</span>
                </div>
                @endforeach
            </div>
        </section>

        {{-- Day of week --}}
        <section class="stats-card">
            <h2 class="stats-card-title">
                <span class="stats-card-icon">✦</span> Best Days to Journal
            </h2>
            <div class="dow-chart">
                @php $maxDow = max(max(array_column($bestDayOfWeek, 'count')), 1); @endphp
                @foreach($bestDayOfWeek as $item)
                <div class="dow-col">
                    <div class="dow-bar-wrap">
                        <div class="dow-bar"
                             style="height: {{ $maxDow > 0 ? round(($item['count'] / $maxDow) * 100) : 0 }}%">
                        </div>
                    </div>
                    <div class="dow-label">{{ $item['day'] }}</div>
                    <div class="dow-count">{{ $item['count'] }}</div>
                </div>
                @endforeach
            </div>
        </section>

    </div>

    {{-- ─── Monthly activity (last 6 months) ──────────────────────────── --}}
    <section class="stats-card">
        <h2 class="stats-card-title">
            <span class="stats-card-icon">◆</span> Monthly Activity
            <span class="stats-card-subtitle">Last 6 months</span>
        </h2>
        <div class="monthly-chart">
            @php $maxMonthly = max(max(array_column($monthlyStats, 'count')), 1); @endphp
            @foreach($monthlyStats as $month)
            <div class="monthly-col">
                <div class="monthly-bar-wrap">
                    <div class="monthly-bar"
                         style="height: {{ $maxMonthly > 0 ? max(4, round(($month['count'] / $maxMonthly) * 120)) : 4 }}px">
                    </div>
                </div>
                <div class="monthly-count">{{ $month['count'] }}</div>
                <div class="monthly-label">{{ $month['label'] }}</div>
            </div>
            @endforeach
        </div>
    </section>

    {{-- ─── Heatmap ─────────────────────────────────────────────────────── --}}
    <section class="stats-card stats-card--heatmap">
        <h2 class="stats-card-title">
            <span class="stats-card-icon">◈</span> {{ now()->format('Y') }} Journal Heatmap
        </h2>
        <div class="heatmap-wrap">
            <div class="heatmap-months">
                @php
                    $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                    foreach($months as $m) echo '<span>' . $m . '</span>';
                @endphp
            </div>
            <div class="heatmap-grid" id="heatmapGrid">
                @php
                    // Pad the start with empty cells for the first week
                    $firstDow = (int)\Carbon\Carbon::create(now()->year, 1, 1)->format('w');
                @endphp
                @for($p = 0; $p < $firstDow; $p++)
                    <div class="heatmap-cell heatmap-cell--pad"></div>
                @endfor
                @foreach($heatmap as $cell)
                <div class="heatmap-cell heatmap-cell--{{ $cell['mood'] ?? 'empty' }}"
                     title="{{ $cell['date'] }}{{ $cell['mood'] ? ': ' . $cell['mood'] : '' }}">
                </div>
                @endforeach
            </div>
            <div class="heatmap-legend">
                <span class="heatmap-legend-label">Less</span>
                <div class="heatmap-cell heatmap-cell--empty" style="width:14px;height:14px;border-radius:3px"></div>
                <div class="heatmap-cell heatmap-cell--neutral" style="width:14px;height:14px;border-radius:3px"></div>
                <div class="heatmap-cell heatmap-cell--positive" style="width:14px;height:14px;border-radius:3px"></div>
                <span class="heatmap-legend-label">More</span>
            </div>
        </div>
    </section>

    {{-- ─── Top keywords ────────────────────────────────────────────────── --}}
    @if(!empty($topKeywords))
    <section class="stats-card">
        <h2 class="stats-card-title">
            <span class="stats-card-icon">✦</span> Most Common Themes
        </h2>
        <div class="keyword-cloud-stats">
            @php $maxKw = max(max(array_values($topKeywords)), 1); @endphp
            @foreach($topKeywords as $kw => $count)
            @php $size = 13 + round(($count / $maxKw) * 14); @endphp
            <span class="kw-tag" style="font-size: {{ $size }}px"
                  title="{{ $count }} {{ Str::plural('mention', $count) }}">
                {{ $kw }}
                <sup class="kw-count">{{ $count }}</sup>
            </span>
            @endforeach
        </div>
    </section>
    @endif

</div>
@endsection