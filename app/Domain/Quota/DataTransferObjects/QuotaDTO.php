<?php

namespace App\Domain\Quota\DataTransferObjects;

use App\Domain\Quota\Models\ProjectQuota;

class QuotaDTO
{
    public function __construct(
        public readonly string $id,
        public readonly int $quota,
        public readonly string $created_at,
        public readonly string $updated_at,
    ) {}

    public static function fromModel(ProjectQuota $quota): self
    {
        return new self(
            id: $quota->id,
            quota: $quota->quota,
            created_at: $quota->created_at->toIso8601String(),
            updated_at: $quota->updated_at->toIso8601String(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'quota' => $this->quota,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
