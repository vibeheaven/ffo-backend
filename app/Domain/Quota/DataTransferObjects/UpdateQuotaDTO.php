<?php

namespace App\Domain\Quota\DataTransferObjects;

class UpdateQuotaDTO
{
    public function __construct(
        public readonly int $quota,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            quota: (int) $data['quota'],
        );
    }
}
