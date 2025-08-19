<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadUser extends Model
{
    protected $fillable = ['lead_id','first_name','last_name','email'];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
