<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'company_number',
        'company_name',
        'source',
        'raw_json',
    ];

    protected $casts = [
        'raw_json' => 'array', // stored as JSON, returned as array
    ];
}
