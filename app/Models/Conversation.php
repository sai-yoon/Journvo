<?php
// app/Models/Conversation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $fillable = ['user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    /**
     * Return messages formatted for the AI context window.
     * Maps sender_type 'ai' → 'assistant' for OpenRouter compatibility.
     */
    public function toAIHistory(int $limit = 30): array
    {
        return $this->messages()
            ->latest()
            ->limit($limit)
            ->get()
            ->reverse()
            ->values()
            ->map(fn($m) => [
                'role'    => $m->sender_type === 'ai' ? 'assistant' : 'user',
                'content' => $m->content,
            ])
            ->toArray();
    }

    /**
     * Get the date this conversation was started.
     */
    public function getDateAttribute(): string
    {
        return $this->created_at->toDateString();
    }
}
