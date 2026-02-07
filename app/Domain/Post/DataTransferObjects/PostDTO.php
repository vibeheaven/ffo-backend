<?php

namespace App\Domain\Post\DataTransferObjects;

class PostDTO
{
    public function __construct(
        // public readonly string $title,
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            // $request->validated('title'),
        );
    }
}
