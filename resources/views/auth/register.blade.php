{{-- resources/views/auth/register.blade.php --}}
@extends('layouts.auth')
@section('title', 'Create Account')

@section('content')
<div class="auth-form-wrap">
    <h2 class="auth-form-title">Start your journal</h2>
    <p class="auth-form-sub">Create an account to begin logging your days</p>

    @if($errors->any())
        <div class="auth-error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="auth-form">
        @csrf

        <div class="form-group">
            <label for="name">Your name</label>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name') }}"
                placeholder="Jane Smith"
                required
                autofocus
                class="{{ $errors->has('name') ? 'input-error' : '' }}"
            >
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="you@example.com"
                required
                class="{{ $errors->has('email') ? 'input-error' : '' }}"
            >
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="At least 8 characters"
                required
            >
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirm password</label>
            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                placeholder="••••••••"
                required
            >
        </div>

        <button type="submit" class="btn-auth">Create Account</button>
    </form>

    <p class="auth-switch">
        Already have an account?
        <a href="{{ route('login') }}">Sign in</a>
    </p>
</div>
@endsection
