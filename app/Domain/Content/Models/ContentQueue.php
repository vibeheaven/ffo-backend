<?php

namespace App\Domain\Content\Models;

use App\Domain\Campaign\Models\Campaign;
use App\Domain\Campaign\Models\CreativeBrief;
use App\Domain\Product\Models\Product;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContentQueue extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'campaign_id',
        'product_id',
        'creative_brief_id',
        'status',
        'version',
        'a_b_variant',
        'scheduled_at',
        'published_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function creativeBrief(): BelongsTo
    {
        return $this->belongsTo(CreativeBrief::class);
    }

    public function generatedContents(): HasMany
    {
        return $this->hasMany(GeneratedContent::class, 'queue_id');
    }
}
