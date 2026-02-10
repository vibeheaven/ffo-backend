<?php

namespace App\Domain\ApiKey\Models;

use App\Domain\Service\Models\Service;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiKey extends Model
{
    protected $fillable = [
        'user_id',
        'service_id',
        'api_key_name',
        'api_key_value',
        'api_secret_value',
        'others_data',
    ];

    /**
     * Şifreli alanlar
     */
    protected $casts = [
        'api_key_value' => 'encrypted',
        'api_secret_value' => 'encrypted',
        'others_data' => 'encrypted:array',
    ];

    /**
     * User ilişkisi
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Service ilişkisi
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
