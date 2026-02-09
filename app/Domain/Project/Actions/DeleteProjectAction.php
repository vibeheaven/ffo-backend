<?php

namespace App\Domain\Project\Actions;

use App\Domain\Project\Models\Project;
use App\Domain\Project\Models\ProjectLog;

class DeleteProjectAction
{
    public function execute(Project $project, string $reason = 'user_request', ?string $description = null): bool
    {
        // Log oluştur
        ProjectLog::create([
            'project_id' => $project->id,
            'action' => 'deleted',
            'reason' => $reason,
            'description' => $description ?? 'Project deleted by user',
        ]);

        // Soft delete yap
        return $project->delete();
    }
}
