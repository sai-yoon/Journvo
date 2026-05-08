<?php
// routes/web.php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StatsController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {

    // Chat
    Route::get('/',           [ChatController::class, 'index'])->name('chat');
    Route::post('/chat/send', [ChatController::class, 'send'])->name('chat.send');

    // Journal
    Route::get('/journal',          [JournalController::class, 'index'])->name('journal.index');
    Route::get('/journal/{date}',   [JournalController::class, 'show'])->name('journal.show')
        ->where('date', '\d{4}-\d{2}-\d{2}');
    Route::post('/journal/compile', [JournalController::class, 'compile'])->name('journal.compile');

    // Stats
    Route::get('/stats', [StatsController::class, 'index'])->name('stats');

    // Settings
    Route::get('/settings',            [SettingsController::class, 'index'])->name('settings');
    Route::patch('/settings/profile',  [SettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::patch('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
    Route::delete('/settings/account', [SettingsController::class, 'destroy'])->name('settings.destroy');

});