<?php

namespace App\Domain\Auth\DataTransferObjects;

use Illuminate\Http\UploadedFile;

class RegisterDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
        public readonly ?string $gender = null,
        public readonly ?string $birthday = null,
        public readonly ?string $location = null,
        public readonly string $language = 'en',
        public readonly ?UploadedFile $profile_photo = null,
    ) {}

    public static function fromRequest(array $data, ?UploadedFile $photo = null): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
            gender: $data['gender'] ?? null,
            birthday: $data['birthday'] ?? null,
            location: $data['location'] ?? null,
            language: $data['language'] ?? 'en',
            profile_photo: $photo,
        );
    }
}
