<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Practices this user owns.
     */
    public function practices(): HasMany
    {
        return $this->hasMany(Practice::class, 'owner_id');
    }

    /**
     * Practices this user is a member of (including the ones they own, if attached in the pivot).
     */
    public function memberPractices(): BelongsToMany
    {
        return $this->belongsToMany(Practice::class, 'practice_user')
            ->withTimestamps()
            ->withPivot('role');
    }
}
