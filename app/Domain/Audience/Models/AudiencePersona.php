<?php

namespace App\Domain\Audience\Models;

use App\Domain\Business\Models\Business;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AudiencePersona extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'business_id',
        'name',
        'age_range',
        'gender_focus',
        'interests',
        'pain_points',
        'desires',
        'objections',
    ];

    protected $casts = [
        'interests' => 'array',
        'pain_points' => 'array',
        'desires' => 'array',
        'objections' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
