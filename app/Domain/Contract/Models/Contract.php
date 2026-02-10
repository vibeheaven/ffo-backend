<?php

namespace App\Domain\Contract\Models;

use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Contract extends Model
{
    protected $fillable = [
        'title',
        'content',
        'type',
        'version',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Kullanıcılar ile ilişki
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_contracts')
            ->withPivot('accepted_at', 'ip_address')
            ->withTimestamps();
    }
}
