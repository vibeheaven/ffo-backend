<?php

namespace App\Domain\Logging\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class RequestLog extends Model
{
    use HasUuids;

    protected $table = 'request_logs';

    protected $fillable = [
        'user_id',
        'method',
        'endpoint',
        'status_code',
        'ip_address',
        'duration_ms',
        'request_payload',
        'response_payload',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
        'duration_ms' => 'integer',
        'status_code' => 'integer',
    ];
}
