<?php

namespace App\Domain\Social\Models;

use App\Domain\Business\Models\Business;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SocialAccount extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'business_id',
        'platform',
        'username',
        'profile_url',
        'bio',
        'posting_frequency',
        'best_posting_hours',
        'hashtags',
    ];

    protected $casts = [
        'best_posting_hours' => 'array',
        'hashtags' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
