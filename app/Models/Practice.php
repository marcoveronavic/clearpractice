<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Practice extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'address_line1',
        'address_line2',
        'city',
        'postcode',
        'country',
        'email',
        'phone',
        'created_by',
    ];
}
