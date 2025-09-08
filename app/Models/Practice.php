<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Practice extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'owner_id',
        'slug',
        'onedrive_drive_id',
        'onedrive_drive_type',
        'onedrive_base_path',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'practice_user')
            ->withTimestamps()
            ->withPivot('role');
    }

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

    public function hasOneDrive(): bool
    {
        return ! empty($this->onedrive_drive_id);
    }

    public function oneDriveDisk()
    {
        if (! $this->hasOneDrive()) {
            throw new \RuntimeException('OneDrive not connected');
        }
        return Storage::build([
            'driver'  => 'msgraph',
            'driveId' => $this->onedrive_drive_id,
        ]);
    }

    public function oneDriveBase(): string
    {
        return $this->onedrive_base_path ? trim($this->onedrive_base_path, '/').'/' : '';
    }
}
