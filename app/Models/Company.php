<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'companies';

    protected $fillable = [
        'number', 'name', 'status', 'type', 'created', 'address',
        'sic_codes', 'accounts', 'confirmation_statement',
        'vat_number', 'authentication_code', 'utr',
        'email', 'telephone', 'vat_period', 'vat_quarter_group',
        'raw', 'saved_at',
    ];

    protected $casts = [
        'sic_codes'               => 'array',
        'accounts'                => 'array',
        'confirmation_statement'  => 'array',
        'raw'                     => 'array',
        'saved_at'                => 'datetime',
    ];
}
