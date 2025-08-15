<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'user_id',
        'deadline',
        'company_number',
        'individual_id',
    ];

    protected $casts = [
        'deadline' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class, 'company_number', 'number');
    }

    public function individual()
    {
        return $this->belongsTo(\App\Models\Individual::class, 'individual_id');
    }
}
