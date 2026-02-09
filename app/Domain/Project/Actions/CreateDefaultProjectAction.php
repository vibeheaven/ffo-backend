<?php

namespace App\Domain\Project\Actions;

use App\Domain\Project\DataTransferObjects\CreateProjectDTO;
use App\Domain\User\Models\User;

class CreateDefaultProjectAction
{
    public function __construct(
        protected CreateProjectAction $createProjectAction
    ) {}

    public function execute(User $user): void
    {
        // Kullanıcının zaten bir projesi var mı kontrol et
        if ($user->projects()->exists()) {
            return;
        }

        // Default proje oluştur (quota kontrolü yapmadan)
        $dto = new CreateProjectDTO(
            name: "{$user->name}'s Project",
            description: 'Default Project'
        );

        $this->createProjectAction->execute($user, $dto, skipQuotaCheck: true);
    }
}
