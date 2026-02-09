<?php

namespace App\Domain\Project\Actions;

use App\Domain\Project\Models\Project;
use App\Domain\Project\Models\ProjectLog;
use App\Domain\Quota\Models\ProjectQuota;

class RestoreProjectAction
{
    public function execute(Project $project, string $reason = 'user_request', ?string $description = null): bool
    {
        // Quota kontrolü
        $quota = ProjectQuota::where('user_id', $project->user_id)->first();
        
        if (!$quota) {
            throw new \Exception("Project quota not found. Please contact support.");
        }

        // Quota 0 ise unlimited (sınırsız) - restore yapılabilir
        if ($quota->quota > 0) {
            // Mevcut aktif proje sayısını kontrol et (soft delete edilmemiş)
            $currentProjectCount = Project::where('user_id', $project->user_id)->count();

            // Quota dolmuşsa restore yapılamaz
            if ($currentProjectCount >= $quota->quota) {
                throw new \Exception("Cannot restore project. Project quota exceeded. You can have up to {$quota->quota} active project(s).");
            }
        }

        // Log oluştur
        ProjectLog::create([
            'project_id' => $project->id,
            'action' => 'restored',
            'reason' => $reason,
            'description' => $description ?? 'Project restored by user',
        ]);

        // Restore yap
        return $project->restore();
    }
}
