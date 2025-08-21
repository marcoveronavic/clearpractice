<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'number',
        'name',
        'status',
        'date_of_creation',
        'address',
        'data',
        // NEW editable fields
        'vat_number',
        'utr',
        'auth_code',
        'vat_period',
        'vat_quarter',
    ];

    protected $casts = [
        'data' => 'array',
        'date_of_creation' => 'date',
    ];
}
