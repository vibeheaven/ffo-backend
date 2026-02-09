<?php

namespace App\Domain\Campaign\Models;

use App\Domain\Business\Models\Business;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'business_id',
        'name',
        'objective',
        'target_platform',
        'video_format',
        'video_duration',
        'daily_video_goal',
        'call_to_action',
        'landing_url',
        'utm_template',
        'disclaimers',
        'forbidden_claims',
    ];

    protected $casts = [
        'forbidden_claims' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function creativeBrief(): HasOne
    {
        return $this->hasOne(CreativeBrief::class);
    }

    public function contentQueues(): HasMany
    {
        return $this->hasMany(ContentQueue::class);
    }
}
