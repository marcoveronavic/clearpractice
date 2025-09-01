<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_number',
        'name',
        'status',
        'company_type',
        'date_of_creation',
        'accounts_next_due',
        'accounts_next_period_end_on',
        'accounts_overdue',
        'confirmation_next_due',
        'confirmation_next_made_up_to',
        'confirmation_overdue',
        'registered_office_address',
        'raw_profile_json',
    ];

    protected $casts = [
        'registered_office_address' => 'array',
        'date_of_creation' => 'date',
        'accounts_next_due' => 'date',
        'accounts_next_period_end_on' => 'date',
        'accounts_overdue' => 'boolean',
        'confirmation_next_due' => 'date',
        'confirmation_next_made_up_to' => 'date',
        'confirmation_overdue' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function deadlines()
    {
        return $this->hasMany(Deadline::class);
    }
}
