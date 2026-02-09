<?php

namespace App\Domain\Project\Models;

use App\Domain\Project\Traits\CascadeRestore;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasUuids, SoftDeletes, CascadeRestore;

    protected static function booted(): void
    {
        static::deleted(function ($project) {
            if ($project->isForceDeleting()) {
                return; // Hard delete - cascade zaten çalışıyor
            }
            
            // Soft delete - cascade soft delete business ve alt kayıtları
            $business = $project->business()->withTrashed()->first();
            if ($business && !$business->trashed()) {
                $business->delete(); // Bu da cascade soft delete'i tetikleyecek
            }
        });
    }

    protected $fillable = [
        'user_id',
        'name',
        'token',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ProjectLog::class);
    }

    public function business(): HasOne
    {
        return $this->hasOne(\App\Domain\Business\Models\Business::class);
    }
}
