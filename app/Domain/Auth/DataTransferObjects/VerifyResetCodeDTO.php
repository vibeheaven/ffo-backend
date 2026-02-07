<?php

namespace App\Domain\Auth\DataTransferObjects;

class VerifyResetCodeDTO
{
    public function __construct(
        public readonly string $phone,
        public readonly string $verification_code,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            phone: $data['phone'],
            verification_code: $data['verification_code'],
        );
    }
}
