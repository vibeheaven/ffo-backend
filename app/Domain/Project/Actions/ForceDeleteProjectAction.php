<?php

namespace App\Domain\Project\Actions;

use App\Domain\Project\Models\Project;
use App\Domain\Project\Models\ProjectLog;

class ForceDeleteProjectAction
{
    public function execute(Project $project, string $reason = 'user_request', ?string $description = null): bool
    {
        // Log oluştur
        ProjectLog::create([
            'project_id' => $project->id,
            'action' => 'force_deleted',
            'reason' => $reason,
            'description' => $description ?? 'Project permanently deleted by user',
        ]);

        // Force delete yap
        return $project->forceDelete();
    }
}
