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
        'time_of_day',   // 'overall' | 'morning' | 'noon' | 'evening'
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

    public function getMoodEmojiAttribute(): string
    {
        return match($this->mood) {
            'positive' => '😊',
            'negative' => '😔',
            default    => '😐',
        };
    }

    public function getPeriodMetaAttribute(): array
    {
        return Conversation::periodLabel($this->time_of_day ?? 'overall');
    }
}
