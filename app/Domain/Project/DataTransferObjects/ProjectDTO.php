<?php

namespace App\Domain\Project\DataTransferObjects;

use App\Domain\Project\Models\Project;

class ProjectDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $token,
        public readonly ?string $description,
        public readonly bool $accessible,
        public readonly string $created_at,
        public readonly string $updated_at,
        public readonly ?string $deleted_at = null,
    ) {}

    public static function fromModel(Project $project): self
    {
        return new self(
            id: $project->id,
            name: $project->name,
            token: $project->token,
            description: $project->description,
            accessible: $project->deleted_at === null,
            created_at: $project->created_at->toIso8601String(),
            updated_at: $project->updated_at->toIso8601String(),
            deleted_at: $project->deleted_at?->toIso8601String(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'token' => $this->token,
            'description' => $this->description,
            'accessible' => $this->accessible,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
