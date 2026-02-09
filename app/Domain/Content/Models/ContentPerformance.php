<?php

namespace App\Domain\Content\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContentPerformance extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'content_id',
        'views',
        'watch_time',
        'completion_rate',
        'clicks',
        'ctr',
        'conversions',
        'cpa',
        'roas',
    ];

    protected $casts = [
        'completion_rate' => 'decimal:2',
        'ctr' => 'decimal:2',
        'cpa' => 'decimal:2',
        'roas' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function content(): BelongsTo
    {
        return $this->belongsTo(GeneratedContent::class, 'content_id');
    }
}
