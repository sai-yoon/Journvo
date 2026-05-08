{{-- resources/views/journal/show.blade.php --}}
@extends('layouts.app')
@section('title', \Carbon\Carbon::parse($date)->format('F j, Y'))

@section('content')
<div class="entry-page">

    <a href="{{ route('journal.index') }}" class="back-link">← Journal</a>

    {{-- Notebook Header --}}
    <header class="notebook-show-header">
        <div class="notebook-show-date">
            <span class="notebook-show-weekday">{{ \Carbon\Carbon::parse($date)->format('l') }}</span>
            <span class="notebook-show-full">{{ \Carbon\Carbon::parse($date)->format('F j, Y') }}</span>
        </div>
        <div class="notebook-show-badge notebook-show-badge--{{ $overall->mood ?? 'neutral' }}">
            <span style="font-size:24px">{{ $overall->mood_emoji }}</span>
            <span>{{ ucfirst($overall->mood ?? 'neutral') }} day</span>
        </div>
    </header>

    {{-- Overall Summary --}}
    <section class="notebook-show-section notebook-show-section--overall">
        <div class="notebook-show-section-label">
            <span class="section-label-icon">✦</span>
            <span>Overall Summary</span>
        </div>
        <blockquote class="entry-summary">{{ $overall->summary }}</blockquote>
        @if(!empty($overall->keywords))
        <div class="keywords-cloud" style="margin-top:16px">
            @foreach($overall->keywords as $i => $kw)
                <span class="keyword keyword--{{ $i % 2 === 0 ? 'a' : 'b' }}">{{ $kw }}</span>
            @endforeach
        </div>
        @endif
    </section>

    {{-- Period Sections --}}
    @if($periods->isNotEmpty())
    <div class="notebook-show-periods">
        <div class="notebook-show-divider">
            <span>Period Summaries</span>
        </div>

        @foreach($periods as $period)
        @php $meta = $period->period_meta; @endphp

        <section class="notebook-show-section notebook-show-section--period">
            <div class="notebook-show-section-label notebook-show-section-label--period">
                <span class="section-label-icon">{{ $meta['emoji'] }}</span>
                <span>{{ $meta['label'] }}</span>
                <span class="section-label-range">{{ $meta['range'] }}</span>
                <span class="section-label-mood">{{ $period->mood_emoji }} {{ ucfirst($period->mood ?? 'neutral') }}</span>
            </div>
            <p class="period-show-summary">{{ $period->summary }}</p>
            @if(!empty($period->keywords))
            <div class="entry-tags" style="margin-top:10px">
                @foreach($period->keywords as $kw)
                    <span class="tag">{{ $kw }}</span>
                @endforeach
            </div>
            @endif
        </section>
        @endforeach
    </div>
    @endif

    {{-- Footer meta --}}
    <div class="entry-meta" style="margin-top:32px">
        <span class="entry-meta-item">{{ \Carbon\Carbon::parse($date)->format('F j, Y') }}</span>
        <span class="entry-meta-sep">·</span>
        <span class="entry-meta-item">{{ $periods->count() }} {{ Str::plural('period', $periods->count()) }} logged</span>
        @if(!empty($overall->keywords))
        <span class="entry-meta-sep">·</span>
        <span class="entry-meta-item">{{ count($overall->keywords) }} themes</span>
        @endif
    </div>

</div>
@endsection