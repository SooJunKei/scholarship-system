<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    protected $fillable = ['email', 'attempts', 'last_attempt_at', 'locked_until'];

    protected $casts = [
        'last_attempt_at' => 'datetime',
        'locked_until' => 'datetime',
        'attempts' => 'integer',
    ];

    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    public function remainingLockoutTime(): int
    {
        if (!$this->isLocked()) {
            return 0;
        }

        return $this->locked_until->diffInMinutes(now());
    }

    public function reset(): void
    {
        $this->update([
            'attempts' => 0,
            'last_attempt_at' => null,
            'locked_until' => null,
        ]);
    }

    public function incrementAttempt(): void
    {
        $this->increment('attempts');
        $this->update(['last_attempt_at' => now()]);

        // Lock after 5 failed attempts for 15 minutes
        if ($this->attempts >= 5) {
            $this->update(['locked_until' => now()->addMinutes(15)]);
        }
    }
}
