<?php

namespace App\Domain\Service\Models;

use App\Domain\ApiKey\Models\ApiKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $fillable = [
        'service_name',
        'service_slug',
        'description',
        'service_form',
        'is_active',
    ];

    protected $casts = [
        'service_form' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * API Keys ilişkisi
     */
    public function apiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class);
    }
}
