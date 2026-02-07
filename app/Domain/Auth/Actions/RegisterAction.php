<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\DataTransferObjects\RegisterDTO;
use App\Domain\User\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterAction
{
    public function execute(RegisterDTO $data): array
    {
        $userData = [
            'name' => $data->name,
            'email' => $data->email,
            'password' => Hash::make($data->password),
            'registration_step' => 5, // Completed
            'language' => $data->language,
        ];

        if ($data->gender) {
            $userData['gender'] = $data->gender;
        }
        if ($data->birthday) {
            $userData['birthday'] = $data->birthday;
        }
        if ($data->location) {
            $userData['location'] = $data->location;
        }

        if ($data->profile_photo) {
            $path = $data->profile_photo->store('profile-photos', 'public');
            $userData['profile_photo_path'] = $path;
        }

        $user = User::create($userData);

        return $user;
    }
}
