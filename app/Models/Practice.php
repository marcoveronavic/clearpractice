<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Practice extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'owner_id',
        'slug',
    ];

    /**
     * Owner of the practice.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Members of the practice.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'practice_user')
            ->withTimestamps()
            ->withPivot('role');
    }

    /**
     * Auto-generate a unique slug when creating.
     */
    protected static function booted(): void
    {
        static::creating(function (Practice $practice) {
            if (! $practice->slug) {
                $base = Str::slug($practice->name) ?: 'practice';
                $slug = $base;
                $i = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $base.'-'.$i++;
                }
                $practice->slug = $slug;
            }
        });
    }
}
