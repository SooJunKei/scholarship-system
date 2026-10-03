<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'email_verified_at', 'is_locked', 'locked_until'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_locked' => 'boolean',
            'locked_until' => 'datetime',
        ];
    }

    public function isAccountLocked(): bool
    {
        if ($this->is_locked && $this->locked_until && $this->locked_until->isFuture()) {
            return true;
        }

        return false;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }
}
