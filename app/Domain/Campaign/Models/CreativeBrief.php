<?php

namespace App\Domain\Campaign\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreativeBrief extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'campaign_id',
        'main_message',
        'supporting_messages',
        'hook_types',
        'script_style',
        'creator_gender',
        'creator_age_range',
        'creator_style',
        'locations',
        'mood',
        'product_usage_style',
    ];

    protected $casts = [
        'supporting_messages' => 'array',
        'hook_types' => 'array',
        'locations' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
}
