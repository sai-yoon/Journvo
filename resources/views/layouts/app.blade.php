{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Journvo — @yield('title', 'Your Daily Journal')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;1,400&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body>

<nav class="sidebar">
    <div class="sidebar-brand">
        <span class="brand-icon">✦</span>
        <span class="brand-name">Journvo</span>
    </div>

    <div class="sidebar-date">
        <div class="date-day">{{ now()->format('l') }}</div>
        <div class="date-full">{{ now()->format('F j, Y') }}</div>
    </div>

    <ul class="sidebar-nav">
        <li>
            <a href="{{ route('chat') }}" class="{{ request()->routeIs('chat') ? 'active' : '' }}">
                <span class="nav-icon">💬</span> Today
            </a>
        </li>
        <li>
            <a href="{{ route('journal.index') }}" class="{{ request()->routeIs('journal.*') ? 'active' : '' }}">
                <span class="nav-icon">📓</span> Journal
            </a>
        </li>
        <li>
            <a href="{{ route('settings') }}" class="{{ request()->routeIs('settings*') ? 'active' : '' }}">
                <span class="nav-icon">⚙</span> Settings
            </a>
        </li>
    </ul>

    <div class="sidebar-compile">
        <button class="btn-compile" id="compileBtn">
            <span class="compile-icon">◎</span> Save Entry
        </button>
    </div>

    <div class="sidebar-user">
        <a href="{{ route('settings') }}" class="sidebar-user-link">
            <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div class="user-info">
                <div class="user-name">{{ auth()->user()->name }}</div>
                <div class="user-email">{{ auth()->user()->email }}</div>
            </div>
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-btn" title="Sign out">⎋</button>
        </form>
    </div>
</nav>

<main class="main-content">
    @yield('content')
</main>

<div class="toast" id="toast" role="alert"></div>

<script>
    window.CHAT_CONFIG = window.CHAT_CONFIG || {};
    window.CHAT_CONFIG.compileUrl = "{{ route('journal.compile') }}";
    window.CHAT_CONFIG.csrfToken  = document.querySelector('meta[name="csrf-token"]').content;
</script>

<script src="{{ asset('js/app.js') }}"></script>
@stack('scripts')
</body>
</html>