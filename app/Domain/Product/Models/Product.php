<?php

namespace App\Domain\Product\Models;

use App\Domain\Business\Models\Business;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'business_id',
        'name',
        'slug',
        'type',
        'category',
        'short_description',
        'long_description',
        'price',
        'discount_price',
        'currency',
        'sku',
        'stock_status',
        'product_url',
        'key_benefits',
        'technical_specs',
        'target_persona_tags',
        'problem_it_solves',
        'objections',
        'faqs',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'key_benefits' => 'array',
        'technical_specs' => 'array',
        'target_persona_tags' => 'array',
        'objections' => 'array',
        'faqs' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(ProductMedia::class);
    }

    public function physicalDetails(): HasOne
    {
        return $this->hasOne(PhysicalProductDetails::class);
    }

    public function digitalDetails(): HasOne
    {
        return $this->hasOne(DigitalProductDetails::class);
    }
}
