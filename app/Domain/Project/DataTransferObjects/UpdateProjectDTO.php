<?php

namespace App\Domain\Project\DataTransferObjects;

class UpdateProjectDTO
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $token = null,
        public readonly ?string $description = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            token: $data['token'] ?? null,
            description: $data['description'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'token' => $this->token,
            'description' => $this->description,
        ], fn($value) => $value !== null);
    }
}
