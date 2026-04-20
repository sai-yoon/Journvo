{{-- resources/views/journal/show.blade.php --}}
@extends('layouts.app')
@section('title', $entry->entry_date->format('F j, Y'))

@section('content')
<div class="entry-page">

    <a href="{{ route('journal.index') }}" class="back-link">← Journal</a>

    <header class="entry-header">
        <div class="entry-header-date">
            <span class="entry-header-day">{{ $entry->entry_date->format('l') }}</span>
            <span class="entry-header-full">{{ $entry->entry_date->format('F j, Y') }}</span>
        </div>
        <div class="entry-header-mood entry-header-mood--{{ $entry->mood ?? 'neutral' }}">
            <span class="mood-emoji-large">{{ $entry->mood_emoji }}</span>
            <span class="mood-label">{{ ucfirst($entry->mood ?? 'neutral') }} day</span>
        </div>
    </header>

    {{-- Summary --}}
    <section class="entry-section">
        <h2 class="entry-section-title">Summary</h2>
        <blockquote class="entry-summary">{{ $entry->summary }}</blockquote>
    </section>

    {{-- Keywords --}}
    @if(!empty($entry->keywords))
    <section class="entry-section">
        <h2 class="entry-section-title">Themes</h2>
        <div class="keywords-cloud">
            @foreach($entry->keywords as $i => $kw)
                <span class="keyword keyword--{{ $i % 2 === 0 ? 'a' : 'b' }}">{{ $kw }}</span>
            @endforeach
        </div>
    </section>
    @endif

    {{-- Meta --}}
    <div class="entry-meta">
        <span class="entry-meta-item">
            Logged on {{ $entry->entry_date->format('F j, Y') }}
        </span>
        <span class="entry-meta-sep">·</span>
        <span class="entry-meta-item">
            {{ ucfirst($entry->mood ?? 'neutral') }} mood
        </span>
        @if(!empty($entry->keywords))
        <span class="entry-meta-sep">·</span>
        <span class="entry-meta-item">
            {{ count($entry->keywords) }} themes
        </span>
        @endif
    </div>

</div>
@endsection