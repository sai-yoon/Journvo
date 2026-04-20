<?php
// app/Models/JournalEntry.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntry extends Model
{
    protected $fillable = [
        'user_id',
        'entry_date',
        'summary',
        'mood',
        'keywords',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'keywords'   => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── Computed helpers (not stored — derived at runtime) ───────────────

    public function getMoodEmojiAttribute(): string
    {
        return match($this->mood) {
            'positive' => '😊',
            'negative' => '😔',
            default    => '😐',
        };
    }

    public function getMoodColorAttribute(): string
    {
        return match($this->mood) {
            'positive' => '#7A9E7E',
            'negative' => '#C47B7B',
            default    => '#8899AA',
        };
    }
}
