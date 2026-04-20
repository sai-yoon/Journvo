{{-- resources/views/auth/login.blade.php --}}
@extends('layouts.auth')
@section('title', 'Sign In')

@section('content')
<div class="auth-form-wrap">
    <h2 class="auth-form-title">Welcome back</h2>
    <p class="auth-form-sub">Sign in to continue your journal</p>

    @if($errors->any())
        <div class="auth-error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="auth-form">
        @csrf

        <div class="form-group">
            <label for="email">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="you@example.com"
                required
                autofocus
                class="{{ $errors->has('email') ? 'input-error' : '' }}"
            >
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="••••••••"
                required
            >
        </div>

        <div class="form-check">
            <input type="checkbox" id="remember" name="remember">
            <label for="remember">Remember me</label>
        </div>

        <button type="submit" class="btn-auth">Sign In</button>
    </form>

    <p class="auth-switch">
        Don't have an account?
        <a href="{{ route('register') }}">Create one</a>
    </p>
</div>
@endsection
