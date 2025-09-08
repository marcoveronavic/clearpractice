<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HmrcToken extends Model
{
    protected $fillable = [
        'user_id','company_id','vrn','access_token','refresh_token','token_type','scope','expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
