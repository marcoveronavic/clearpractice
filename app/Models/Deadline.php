<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deadline extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'type',
        'period_start_on',
        'period_end_on',
        'due_on',
        'filed_on',
        'status',
        'source',
        'notes',
    ];

    protected $casts = [
        'period_start_on' => 'date',
        'period_end_on'   => 'date',
        'due_on'          => 'date',
        'filed_on'        => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}

