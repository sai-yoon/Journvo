{{-- resources/views/journal/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Journal')

@section('content')
<div class="journal-page">

    <header class="journal-header">
        <h1 class="journal-title">Your Journal</h1>
        <p class="journal-subtitle">{{ $entries->total() }} {{ Str::plural('entry', $entries->total()) }} recorded</p>
    </header>

    {{-- Mood strip — last 7 days --}}
    @if($recentMoods->count() > 0)
    <section class="mood-strip">
        <div class="mood-strip-label">Past 7 days</div>
        <div class="mood-bars">
            @foreach($recentMoods->reverse() as $day)
                <div class="mood-bar-wrapper" title="{{ $day->entry_date->format('M j') }}: {{ $day->mood ?? 'neutral' }}">
                    <div class="mood-bar mood-bar--{{ $day->mood ?? 'neutral' }}"></div>
                    <div class="mood-bar-day">{{ $day->entry_date->format('D') }}</div>
                </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- Entries list --}}
    <div class="entries-list">
        @forelse($entries as $entry)
        <a href="{{ route('journal.show', $entry->entry_date->toDateString()) }}"
           class="entry-card entry-card--{{ $entry->mood ?? 'neutral' }}">

            <div class="entry-card-left">
                <div class="entry-mood-emoji">{{ $entry->mood_emoji }}</div>
                <div class="entry-date-block">
                    <span class="entry-day">{{ $entry->entry_date->format('d') }}</span>
                    <span class="entry-month">{{ $entry->entry_date->format('M') }}</span>
                </div>
            </div>

            <div class="entry-card-body">
                <div class="entry-weekday">{{ $entry->entry_date->format('l') }}</div>
                <p class="entry-summary-preview">{{ Str::limit($entry->summary, 110) }}</p>
                @if(!empty($entry->keywords))
                <div class="entry-tags">
                    @foreach(array_slice($entry->keywords, 0, 4) as $kw)
                        <span class="tag">{{ $kw }}</span>
                    @endforeach
                </div>
                @endif
            </div>

            <div class="entry-card-right">
                <span class="entry-arrow">→</span>
            </div>
        </a>
        @empty
        <div class="empty-journal">
            <div class="empty-icon">📓</div>
            <p>No journal entries yet.<br>Start chatting and hit <strong>Save Entry</strong>.</p>
            <a href="{{ route('chat') }}" class="btn-primary">Go to Chat</a>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($entries->hasPages())
    <nav class="pagination">
        @if($entries->onFirstPage())
            <span class="page-btn page-btn--disabled">← Newer</span>
        @else
            <a href="{{ $entries->previousPageUrl() }}" class="page-btn">← Newer</a>
        @endif
        <span class="page-info">Page {{ $entries->currentPage() }} of {{ $entries->lastPage() }}</span>
        @if($entries->hasMorePages())
            <a href="{{ $entries->nextPageUrl() }}" class="page-btn">Older →</a>
        @else
            <span class="page-btn page-btn--disabled">Older →</span>
        @endif
    </nav>
    @endif

</div>
@endsection