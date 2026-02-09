<?php

namespace App\Domain\Content\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GeneratedContent extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'queue_id',
        'script_text',
        'hook_text',
        'caption_text',
        'cta_text',
        'video_prompt_data',
        'performance_score',
    ];

    protected $casts = [
        'video_prompt_data' => 'array',
        'performance_score' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function queue(): BelongsTo
    {
        return $this->belongsTo(ContentQueue::class, 'queue_id');
    }

    public function performances(): HasMany
    {
        return $this->hasMany(ContentPerformance::class, 'content_id');
    }
}
