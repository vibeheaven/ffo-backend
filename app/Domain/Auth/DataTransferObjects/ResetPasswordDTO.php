<?php

namespace App\Domain\Auth\DataTransferObjects;

class ResetPasswordDTO
{
    public function __construct(
        public readonly string $phone,
        public readonly string $reset_token,
        public readonly string $password,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            phone: $data['phone'],
            reset_token: $data['reset_token'],
            password: $data['password'],
        );
    }
}
