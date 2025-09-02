<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    // Allow both the legacy and the modern columns to be mass assigned.
    protected $fillable = [
        // legacy columns still present in your DB
        'number',                      // ← critical: include this so inserts set it

        // current columns used by the importer
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
        'registered_office_address'     => 'array',
        'date_of_creation'              => 'date',
        'accounts_next_due'             => 'date',
        'accounts_next_period_end_on'   => 'date',
        'accounts_overdue'              => 'boolean',
        'confirmation_next_due'         => 'date',
        'confirmation_next_made_up_to'  => 'date',
        'confirmation_overdue'          => 'boolean',
    ];
}
