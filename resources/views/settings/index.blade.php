{{-- resources/views/settings/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Account Settings')

@section('content')
<div class="settings-page">

    {{-- ─── Page Header ──────────────────────────────────────────────── --}}
    <header class="settings-header">
        <div class="settings-header-left">
            <h1 class="settings-title">Account Settings</h1>
            <p class="settings-subtitle">Manage your profile, password, and account data</p>
        </div>
        <div class="settings-avatar-lg">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
    </header>

    <div class="settings-grid">

        {{-- ─── Profile Card ─────────────────────────────────────────── --}}
        <section class="settings-card" id="profile">
            <div class="settings-card-header">
                <div class="settings-card-icon">✦</div>
                <div>
                    <h2 class="settings-card-title">Profile</h2>
                    <p class="settings-card-desc">Update your name and email address</p>
                </div>
            </div>

            @if(session('profile_success'))
                <div class="settings-alert settings-alert--success">
                    ✓ {{ session('profile_success') }}
                </div>
            @endif

            @if($errors->hasBag('default') && !$errors->hasBag('password'))
                <div class="settings-alert settings-alert--error">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('settings.profile') }}" class="settings-form">
                @csrf
                @method('PATCH')

                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Full name</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name', $user->name) }}"
                            required
                            autocomplete="name"
                        >
                    </div>
                    <div class="form-group">
                        <label for="email">Email address</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email', $user->email) }}"
                            required
                            autocomplete="email"
                        >
                    </div>
                </div>

                <div class="settings-form-footer">
                    <div class="settings-member-since">
                        Member since {{ $user->created_at->format('F Y') }}
                    </div>
                    <button type="submit" class="btn-settings-save">
                        Save Profile
                    </button>
                </div>
            </form>
        </section>

        {{-- ─── Password Card ─────────────────────────────────────────── --}}
        <section class="settings-card" id="password">
            <div class="settings-card-header">
                <div class="settings-card-icon">⬡</div>
                <div>
                    <h2 class="settings-card-title">Password</h2>
                    <p class="settings-card-desc">Choose a strong password to keep your journal secure</p>
                </div>
            </div>

            @if(session('password_success'))
                <div class="settings-alert settings-alert--success">
                    ✓ {{ session('password_success') }}
                </div>
            @endif

            @if($errors->hasBag('updatePassword'))
                <div class="settings-alert settings-alert--error">
                    {{ $errors->getBag('updatePassword')->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('settings.password') }}" class="settings-form">
                @csrf
                @method('PATCH')

                <div class="form-group">
                    <label for="current_password">Current password</label>
                    <div class="input-password-wrap">
                        <input
                            type="password"
                            id="current_password"
                            name="current_password"
                            placeholder="••••••••"
                            autocomplete="current-password"
                        >
                        <button type="button" class="toggle-password" data-target="current_password">
                            <span class="eye-icon">◎</span>
                        </button>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">New password</label>
                        <div class="input-password-wrap">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="At least 8 characters"
                                autocomplete="new-password"
                            >
                            <button type="button" class="toggle-password" data-target="password">
                                <span class="eye-icon">◎</span>
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation">Confirm new password</label>
                        <div class="input-password-wrap">
                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                placeholder="••••••••"
                                autocomplete="new-password"
                            >
                            <button type="button" class="toggle-password" data-target="password_confirmation">
                                <span class="eye-icon">◎</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Password strength indicator --}}
                <div class="password-strength" id="passwordStrength" style="display:none">
                    <div class="strength-bars">
                        <span class="strength-bar" id="bar1"></span>
                        <span class="strength-bar" id="bar2"></span>
                        <span class="strength-bar" id="bar3"></span>
                        <span class="strength-bar" id="bar4"></span>
                    </div>
                    <span class="strength-label" id="strengthLabel">Weak</span>
                </div>

                <div class="settings-form-footer">
                    <p class="settings-hint">Minimum 8 characters</p>
                    <button type="submit" class="btn-settings-save">
                        Update Password
                    </button>
                </div>
            </form>
        </section>

        {{-- ─── Stats link card ───────────────────────────────────────── --}}
        <section class="settings-card settings-card--stats-link">
            <div class="settings-card-header">
                <div class="settings-card-icon">◈</div>
                <div>
                    <h2 class="settings-card-title">Journal Stats</h2>
                    <p class="settings-card-desc">View your streaks, mood charts, heatmap and more</p>
                </div>
                <a href="{{ route('stats') }}" class="btn-settings-save" style="margin-left:auto">
                    View Stats →
                </a>
            </div>
        </section>

        {{-- ─── Danger Zone Card ──────────────────────────────────────── --}}
        <section class="settings-card settings-card--danger" id="danger">
            <div class="settings-card-header">
                <div class="settings-card-icon settings-card-icon--danger">✕</div>
                <div>
                    <h2 class="settings-card-title settings-card-title--danger">Danger Zone</h2>
                    <p class="settings-card-desc">Permanently delete your account and all journal data</p>
                </div>
            </div>

            @if($errors->hasBag('deleteAccount'))
                <div class="settings-alert settings-alert--error">
                    {{ $errors->getBag('deleteAccount')->first() }}
                </div>
            @endif

            <div class="danger-warning">
                <p>This action <strong>cannot be undone</strong>. All your conversations, journal entries, and account data will be permanently deleted.</p>
            </div>

            {{-- Collapsed by default, revealed by button --}}
            <div id="deleteFormWrap" style="display:none">
                <form method="POST" action="{{ route('settings.destroy') }}" class="settings-form">
                    @csrf
                    @method('DELETE')
                    <div class="form-group">
                        <label for="confirm_delete">
                            Type <strong>DELETE</strong> to confirm
                        </label>
                        <input
                            type="text"
                            id="confirm_delete"
                            name="confirm_delete"
                            placeholder="DELETE"
                            autocomplete="off"
                            class="input-danger"
                        >
                    </div>
                    <div class="settings-form-footer">
                        <button type="button" class="btn-settings-cancel" id="cancelDelete">
                            Cancel
                        </button>
                        <button type="submit" class="btn-settings-danger" id="confirmDeleteBtn" disabled>
                            Delete My Account
                        </button>
                    </div>
                </form>
            </div>

            <div id="deleteToggleWrap">
                <button class="btn-settings-danger-outline" id="showDeleteForm">
                    I want to delete my account
                </button>
            </div>
        </section>

    </div>{{-- /settings-grid --}}
</div>{{-- /settings-page --}}
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    // ── Toggle password visibility ─────────────────────────────────────────
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', () => {
            const target = document.getElementById(btn.dataset.target);
            const isHidden = target.type === 'password';
            target.type = isHidden ? 'text' : 'password';
            btn.querySelector('.eye-icon').textContent = isHidden ? '◉' : '◎';
        });
    });

    // ── Password strength meter ────────────────────────────────────────────
    const passwordInput  = document.getElementById('password');
    const strengthWrap   = document.getElementById('passwordStrength');
    const strengthLabel  = document.getElementById('strengthLabel');
    const bars           = [
        document.getElementById('bar1'),
        document.getElementById('bar2'),
        document.getElementById('bar3'),
        document.getElementById('bar4'),
    ];

    const levels = [
        { label: 'Weak',      color: 'var(--rose)',     fill: 1 },
        { label: 'Fair',      color: 'var(--amber)',    fill: 2 },
        { label: 'Good',      color: 'var(--amber-lt)', fill: 3 },
        { label: 'Strong',    color: 'var(--sage)',     fill: 4 },
    ];

    function scorePassword(p) {
        let score = 0;
        if (p.length >= 8)  score++;
        if (p.length >= 12) score++;
        if (/[A-Z]/.test(p) && /[a-z]/.test(p)) score++;
        if (/[0-9]/.test(p)) score++;
        if (/[^A-Za-z0-9]/.test(p)) score++;
        return Math.min(Math.floor(score / 1.25), 3);
    }

    if (passwordInput) {
        passwordInput.addEventListener('input', () => {
            const val = passwordInput.value;
            if (!val) { strengthWrap.style.display = 'none'; return; }

            strengthWrap.style.display = 'flex';
            const level = scorePassword(val);
            const { label, color, fill } = levels[level];

            bars.forEach((bar, i) => {
                bar.style.background = i < fill ? color : 'var(--blush)';
            });
            strengthLabel.textContent = label;
            strengthLabel.style.color = color;
        });
    }

    // ── Delete account form toggle ─────────────────────────────────────────
    const showBtn       = document.getElementById('showDeleteForm');
    const cancelBtn     = document.getElementById('cancelDelete');
    const deleteWrap    = document.getElementById('deleteFormWrap');
    const toggleWrap    = document.getElementById('deleteToggleWrap');
    const confirmInput  = document.getElementById('confirm_delete');
    const confirmBtn    = document.getElementById('confirmDeleteBtn');

    showBtn?.addEventListener('click', () => {
        deleteWrap.style.display  = 'block';
        toggleWrap.style.display  = 'none';
        confirmInput?.focus();
    });

    cancelBtn?.addEventListener('click', () => {
        deleteWrap.style.display  = 'block';
        toggleWrap.style.display  = 'block';
        deleteWrap.style.display  = 'none';
        if (confirmInput) confirmInput.value = '';
        if (confirmBtn)   confirmBtn.disabled = true;
    });

    // Only enable the delete button when user types DELETE exactly
    confirmInput?.addEventListener('input', () => {
        confirmBtn.disabled = confirmInput.value !== 'DELETE';
    });

});
</script>
@endpush