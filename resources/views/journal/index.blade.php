{{-- resources/views/journal/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Journal')

@section('content')
<div class="journal-page">

    <header class="journal-header">
        <h1 class="journal-title">Your Journal</h1>
        <p class="journal-subtitle">{{ $notebooks->total() }} {{ Str::plural('notebook', $notebooks->total()) }}</p>
    </header>

    {{-- Mood strip --}}
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

    {{-- Notebooks --}}
    <div class="notebooks-list">
        @forelse($notebooks as $notebook)
        @php
            $dateStr  = $notebook->entry_date->toDateString();
            $periods  = $periodEntries[$dateStr] ?? collect();
            $hasMorning = $periods->firstWhere('time_of_day', 'morning');
            $hasNoon    = $periods->firstWhere('time_of_day', 'noon');
            $hasEvening = $periods->firstWhere('time_of_day', 'evening');
        @endphp

        <article class="notebook notebook--{{ $notebook->mood ?? 'neutral' }}">

            {{-- Notebook top bar --}}
            <div class="notebook-topbar">
                <div class="notebook-date-block">
                    <span class="notebook-weekday">{{ $notebook->entry_date->format('l') }}</span>
                    <span class="notebook-date">{{ $notebook->entry_date->format('F j, Y') }}</span>
                </div>
                <div class="notebook-meta">
                    <span class="notebook-mood-emoji">{{ $notebook->mood_emoji }}</span>
                    <a href="{{ route('journal.show', $dateStr) }}" class="notebook-open-btn">
                        Open notebook →
                    </a>
                </div>
            </div>

            {{-- Overall summary --}}
            <div class="notebook-overall">
                <p class="notebook-summary">{{ Str::limit($notebook->summary, 160) }}</p>
                @if(!empty($notebook->keywords))
                <div class="notebook-tags">
                    @foreach(array_slice($notebook->keywords, 0, 5) as $kw)
                        <span class="tag">{{ $kw }}</span>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Period pills --}}
            <div class="notebook-periods">
                @foreach([
                    ['morning', '🌅', 'Morning',   $hasMorning],
                    ['noon',    '☀️',  'Afternoon', $hasNoon],
                    ['evening', '🌙', 'Evening',   $hasEvening],
                ] as [$key, $emoji, $label, $entry])

                <button
                    class="period-pill period-pill--{{ $key }} {{ $entry ? '' : 'period-pill--empty' }}"
                    data-date="{{ $dateStr }}"
                    data-period="{{ $key }}"
                    {{ $entry ? '' : 'disabled' }}
                    onclick="togglePeriod(this)"
                    aria-expanded="false"
                >
                    <span class="period-pill-emoji">{{ $emoji }}</span>
                    <span class="period-pill-label">{{ $label }}</span>
                    @if($entry)
                        <span class="period-pill-mood">{{ $entry->mood_emoji }}</span>
                        <span class="period-pill-chevron">▾</span>
                    @else
                        <span class="period-pill-empty-label">No entry</span>
                    @endif
                </button>

                @endforeach
            </div>

            {{-- Period detail panels (hidden by default) --}}
            @foreach([
                ['morning', '🌅', 'Morning',   $hasMorning],
                ['noon',    '☀️',  'Afternoon', $hasNoon],
                ['evening', '🌙', 'Evening',   $hasEvening],
            ] as [$key, $emoji, $label, $entry])

            @if($entry)
            <div class="period-panel" id="panel-{{ $dateStr }}-{{ $key }}" hidden>
                <div class="period-panel-header">
                    <span>{{ $emoji }} {{ $label }}</span>
                    <span class="period-panel-mood">{{ $entry->mood_emoji }} {{ ucfirst($entry->mood ?? 'neutral') }}</span>
                </div>
                <p class="period-panel-summary">{{ $entry->summary }}</p>
                @if(!empty($entry->keywords))
                <div class="period-panel-tags">
                    @foreach($entry->keywords as $kw)
                        <span class="tag tag--sm">{{ $kw }}</span>
                    @endforeach
                </div>
                @endif
            </div>
            @endif

            @endforeach

        </article>
        @empty
        <div class="empty-journal">
            <div class="empty-icon">📓</div>
            <p>No notebooks yet.<br>Chat and hit <strong>Save Entry</strong> to create your first one.</p>
            <a href="{{ route('chat') }}" class="btn-primary">Go to Chat</a>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($notebooks->hasPages())
    <nav class="pagination">
        @if($notebooks->onFirstPage())
            <span class="page-btn page-btn--disabled">← Newer</span>
        @else
            <a href="{{ $notebooks->previousPageUrl() }}" class="page-btn">← Newer</a>
        @endif
        <span class="page-info">Page {{ $notebooks->currentPage() }} of {{ $notebooks->lastPage() }}</span>
        @if($notebooks->hasMorePages())
            <a href="{{ $notebooks->nextPageUrl() }}" class="page-btn">Older →</a>
        @else
            <span class="page-btn page-btn--disabled">Older →</span>
        @endif
    </nav>
    @endif

</div>
@endsection

@push('scripts')
<script>
function togglePeriod(btn) {
    const date   = btn.dataset.date;
    const period = btn.dataset.period;
    const panel  = document.getElementById(`panel-${date}-${period}`);
    if (!panel) return;

    const isOpen = btn.getAttribute('aria-expanded') === 'true';

    // Close all other open panels in this notebook
    const notebook = btn.closest('.notebook');
    notebook.querySelectorAll('.period-pill[aria-expanded="true"]').forEach(b => {
        if (b !== btn) {
            b.setAttribute('aria-expanded', 'false');
            const p = document.getElementById(`panel-${b.dataset.date}-${b.dataset.period}`);
            if (p) closePanel(p);
        }
    });

    if (isOpen) {
        btn.setAttribute('aria-expanded', 'false');
        closePanel(panel);
    } else {
        btn.setAttribute('aria-expanded', 'true');
        openPanel(panel);
    }
}

function openPanel(panel) {
    panel.hidden = false;
    panel.style.maxHeight = '0';
    panel.style.opacity   = '0';
    requestAnimationFrame(() => {
        panel.style.transition = 'max-height .35s cubic-bezier(.4,0,.2,1), opacity .25s ease';
        panel.style.maxHeight  = panel.scrollHeight + 'px';
        panel.style.opacity    = '1';
    });
}

function closePanel(panel) {
    panel.style.maxHeight = '0';
    panel.style.opacity   = '0';
    panel.addEventListener('transitionend', () => {
        panel.hidden = true;
        panel.style.transition = '';
    }, { once: true });
}
</script>
@endpush