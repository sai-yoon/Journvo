<?php
// routes/web.php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

// ─── Guest only ───────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[AuthController::class, 'register']);
});

// ─── Logout ───────────────────────────────────────────────────────────────────
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// ─── Authenticated ────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Chat
    Route::get('/',           [ChatController::class, 'index'])->name('chat');
    Route::post('/chat/send', [ChatController::class, 'send'])->name('chat.send');

    // Journal
    Route::get('/journal',          [JournalController::class, 'index'])->name('journal.index');
    Route::get('/journal/{date}',   [JournalController::class, 'show'])->name('journal.show')
        ->where('date', '\d{4}-\d{2}-\d{2}');
    Route::post('/journal/compile', [JournalController::class, 'compile'])->name('journal.compile');

    // Settings
    Route::get('/settings',            [SettingsController::class, 'index'])->name('settings');
    Route::patch('/settings/profile',  [SettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::patch('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
    Route::delete('/settings/account', [SettingsController::class, 'destroy'])->name('settings.destroy');

});