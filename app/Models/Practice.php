<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Practice extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'owner_id',
    ];

    /**
     * The owner of the practice (User).
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Members (users) who belong to this practice (including the owner).
     * Pivot has a "role" column ('admin' | 'member'). Ownership is tracked by owner_id.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'practice_user')
            ->withTimestamps()
            ->withPivot('role');
    }

    /**
     * Optional alias so $practice->users behaves like $practice->members.
     */
    public function users(): BelongsToMany
    {
        return $this->members();
    }

    /**
     * Use slug in routes (e.g. /practices/my-practice and /{practice:slug}/...).
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
