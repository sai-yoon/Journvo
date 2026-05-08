<?php
// app/Models/Conversation.php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
 
class Conversation extends Model
{
    protected $fillable = ['user_id', 'time_of_day'];
 
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
 
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }
 
    /**
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
 
    public function getDateAttribute(): string
    {
        return $this->created_at->toDateString();
    }
 
    /**
     * Resolve which time period a given hour falls into.
     */
    public static function resolvePeriod(?int $hour = null): string
    {
        $hour ??= (int) now()->format('G');
 
        if ($hour >= 5 && $hour < 12)  return 'morning';
        if ($hour >= 12 && $hour < 17) return 'noon';
        return 'evening'; // 17:00–23:59 and 00:00–04:59
    }
 
    /**
     * Human-readable label + emoji for a period.
     */
    public static function periodLabel(string $period): array
    {
        return match($period) {
            'morning' => ['label' => 'Morning',  'emoji' => '🌅', 'range' => '5 AM – 12 PM'],
            'noon'    => ['label' => 'Afternoon', 'emoji' => '☀️', 'range' => '12 PM – 5 PM'],
            'evening' => ['label' => 'Evening',  'emoji' => '🌙', 'range' => '5 PM – midnight'],
            default   => ['label' => 'Overall',  'emoji' => '✦',  'range' => 'Full day'],
        };
    }
}