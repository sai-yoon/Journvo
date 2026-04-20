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
 
    protected $hidden = ['password', 'remember_token'];
 
    protected $casts = ['password' => 'hashed'];
 
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }
 
    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }
 
    /**
     * Get or create today's conversation using the app's configured
     * timezone — so "today" always means the user's local date,
     * not UTC.
     */
    public function todaysConversation(): Conversation
    {
        // now() respects the timezone set in config/app.php
        $todayDate = now()->toDateString();
 
        $conversation = $this->conversations()
            ->whereDate('created_at', $todayDate)
            ->latest()
            ->first();
 
        if (!$conversation) {
            $conversation = $this->conversations()->create();
        }
 
        return $conversation;
    }
}
