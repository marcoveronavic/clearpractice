<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // … existing code …

    public function companies()
    {
        // many-to-many: users ↔ companies through company_user
        return $this->belongsToMany(Company::class)->withTimestamps();
    }
}
