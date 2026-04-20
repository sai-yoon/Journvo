{{-- resources/views/layouts/auth.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Journvo — @yield('title')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;1,400&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="auth-body">

<div class="auth-split">

    {{-- Left panel: branding --}}
    <div class="auth-panel auth-panel--brand">
        <div class="auth-brand">
            <span class="brand-icon-lg">✦</span>
            <h1 class="auth-brand-name">Journvo</h1>
            <p class="auth-brand-tagline">Your private space to reflect,<br>remember, and grow.</p>
        </div>
        <div class="auth-panel-deco" aria-hidden="true">
            <span>Today I felt…</span>
            <span>Something good happened…</span>
            <span>I want to remember…</span>
        </div>
    </div>

    {{-- Right panel: form --}}
    <div class="auth-panel auth-panel--form">
        @yield('content')
    </div>

</div>

</body>
</html>
