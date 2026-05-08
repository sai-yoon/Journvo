<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'email', 'password'];
    protected $hidden   = ['password', 'remember_token'];
    protected $casts    = ['password' => 'hashed'];

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }

    /**
     * Get or create a conversation for the current user,
     * today's date, and the current time period.
     */
    public function todaysConversation(): Conversation
    {
        $period    = Conversation::resolvePeriod();
        $todayDate = now()->toDateString();

        $conversation = $this->conversations()
            ->whereDate('created_at', $todayDate)
            ->where('time_of_day', $period)
            ->latest()
            ->first();

        if (!$conversation) {
            $conversation = $this->conversations()->create([
                'time_of_day' => $period,
            ]);
        }

        return $conversation;
    }
}