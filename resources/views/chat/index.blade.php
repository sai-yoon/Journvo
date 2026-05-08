{{-- resources/views/chat/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Today — ' . now()->format('M j'))
 
@section('content')
<div class="chat-page">
 
    <header class="chat-header">
        <div class="chat-header-info">
            <div class="period-badge period-badge--{{ $period }}">
                <span class="period-badge-emoji">{{ $periodMeta['emoji'] }}</span>
                <span class="period-badge-label">{{ $periodMeta['label'] }}</span>
                <span class="period-badge-range">{{ $periodMeta['range'] }}</span>
            </div>
            <h1 class="chat-title">How's your {{ strtolower($periodMeta['label']) }} going?</h1>
            <p class="chat-subtitle" id="chatSubtitle">
                {{ $messages->count() > 0
                    ? $messages->count() . ' messages this ' . $period
                    : 'Tell me about your ' . $period . '…' }}
            </p>
        </div>
        <div class="mood-indicator" id="moodIndicator" title="Current mood">
            <span class="mood-dot"></span>
        </div>
    </header>
 
    <div class="messages-container" id="messagesContainer">
 
        @if($messages->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">{{ $periodMeta['emoji'] }}</div>
                <p>Your {{ strtolower($periodMeta['label']) }} is a blank page.<br>Tell me what's on your mind.</p>
            </div>
        @else
            @foreach($messages as $message)
                <div class="message message--{{ $message->sender_type === 'ai' ? 'ai' : 'user' }}">
                    <div class="message-bubble">{{ $message->content }}</div>
                    <div class="message-time">{{ $message->created_at->format('g:i A') }}</div>
                </div>
            @endforeach
        @endif
 
        <div class="message message--ai typing-indicator" id="typingIndicator" style="display:none">
            <div class="message-bubble">
                <span class="dot"></span><span class="dot"></span><span class="dot"></span>
            </div>
        </div>
    </div>
 
    <div class="chat-input-area">
        <div class="input-wrapper">
            <textarea
                id="messageInput"
                class="message-input"
                placeholder="Write anything about your {{ strtolower($periodMeta['label']) }}…"
                rows="1"
                maxlength="1000"
                autofocus
            ></textarea>
            <button class="send-btn" id="sendBtn" aria-label="Send message">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                    <path d="M22 2L11 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M22 2L15 22L11 13L2 9L22 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
        <div class="input-hint">Press <kbd>Enter</kbd> to send · <kbd>Shift+Enter</kbd> for new line</div>
    </div>
 
</div>
@endsection
 
@push('scripts')
<script src="{{ asset('js/chat.js') }}"></script>
<script>
    window.CHAT_CONFIG = window.CHAT_CONFIG || {};
    window.CHAT_CONFIG.sendUrl    = "{{ route('chat.send') }}";
    window.CHAT_CONFIG.compileUrl = "{{ route('journal.compile') }}";
    window.CHAT_CONFIG.csrfToken  = document.querySelector('meta[name="csrf-token"]').content;
    window.CHAT_CONFIG.period     = "{{ $period }}";
</script>
@endpush