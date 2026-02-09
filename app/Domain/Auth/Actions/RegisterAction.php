<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\DataTransferObjects\RegisterDTO;
use App\Domain\Project\Actions\CreateDefaultProjectAction;
use App\Domain\Quota\Actions\CreateDefaultQuotaAction;
use App\Domain\User\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterAction
{
    public function __construct(
        protected CreateDefaultProjectAction $createDefaultProjectAction,
        protected CreateDefaultQuotaAction $createDefaultQuotaAction
    ) {}

    public function execute(RegisterDTO $data): User
    {
        $userData = [
            'name' => $data->name,
            'email' => $data->email,
            'password' => Hash::make($data->password),
            'registration_step' => 5, 
        ];

        $user = User::create($userData);

        // Default quota oluştur
        $this->createDefaultQuotaAction->execute($user);

        // Default proje oluştur
        $this->createDefaultProjectAction->execute($user);

        return $user;
    }
}
