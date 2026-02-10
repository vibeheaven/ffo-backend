<?php

namespace App\Domain\User\DataTransferObjects;

use App\Domain\User\Models\User;

class UserDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly int $registration_step,
        public readonly ?string $phone = null,
        public readonly ?string $phone_verified_at = null,
        public readonly ?string $gender = null,
        public readonly ?string $birthday = null,
        public readonly ?string $location = null,
        public readonly ?string $language = null,
        public readonly ?string $profile_photo_path = null,
    ) {}

    public static function fromModel(User $user): self
    {
        return new self(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            registration_step: $user->registration_step,
            phone: $user->phone,
            phone_verified_at: $user->phone_verified_at?->format('Y-m-d H:i:s'),
            gender: $user->gender,
            birthday: $user->birthday?->format('Y-m-d'),
            location: $user->location,
            language: $user->language,
            profile_photo_path: $user->profile_photo_path,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'registration_step' => $this->registration_step,
            'phone' => $this->phone,
            'phone_verified_at' => $this->phone_verified_at,
            'gender' => $this->gender,
            'birthday' => $this->birthday,
            'location' => $this->location,
            'language' => $this->language,
            'profile_photo_path' => $this->profile_photo_path,
        ];
    }

    public function toBasicArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'phone_verified_at' => $this->phone_verified_at,
        ];
    }
}
