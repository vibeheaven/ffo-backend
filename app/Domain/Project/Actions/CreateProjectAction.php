<?php

namespace App\Domain\Project\Actions;

use App\Domain\Project\DataTransferObjects\CreateProjectDTO;
use App\Domain\Project\Models\Project;
use App\Domain\Project\Models\ProjectLog;
use App\Domain\Quota\Models\ProjectQuota;
use App\Domain\User\Models\User;
use Illuminate\Support\Str;

class CreateProjectAction
{
    public function execute(User $user, CreateProjectDTO $dto, bool $skipQuotaCheck = false): Project
    {
        // Quota kontrolü (default proje oluştururken skip edilebilir)
        if (!$skipQuotaCheck) {
            $quota = ProjectQuota::where('user_id', $user->id)->first();
            
            if (!$quota) {
                // Quota yoksa proje eklenemez
                throw new \Exception("Project quota not found. Please contact support.");
            }

            // Quota 0 ise unlimited (sınırsız)
            if ($quota->quota > 0) {
                // Mevcut aktif proje sayısını kontrol et (soft delete edilmemiş)
                $currentProjectCount = Project::where('user_id', $user->id)->count();

                // Eğer quota aşılmışsa, yeni eklenmeden önce fazla projeleri sil
                // Örnek: quota=1, mevcut=3 -> 3-1=2 proje silinmeli, 1 proje kalacak, sonra yeni eklenecek -> toplam 1 olacak
                if ($currentProjectCount > $quota->quota) {
                    // Kaç proje silinmeli? (Quota kadar kalmalı, yeni eklenecek için)
                    $projectsToDelete = $currentProjectCount - $quota->quota;
                    
                    // Rastgele projeleri soft delete yap
                    $projectsToSoftDelete = Project::where('user_id', $user->id)
                        ->inRandomOrder()
                        ->limit($projectsToDelete)
                        ->get();
                    
                    foreach ($projectsToSoftDelete as $project) {
                        // Log oluştur
                        ProjectLog::create([
                            'project_id' => $project->id,
                            'action' => 'deleted',
                            'reason' => 'quota_exceeded',
                            'description' => "Project automatically deleted due to quota limit. Quota: {$quota->quota}, Current projects: {$currentProjectCount}",
                        ]);
                        
                        $project->delete();
                    }
                    
                    // Silme işleminden sonra tekrar kontrol et
                    $currentProjectCount = Project::where('user_id', $user->id)->count();
                }

                // Quota dolmuşsa yeni proje eklenemez
                if ($currentProjectCount >= $quota->quota) {
                    throw new \Exception("Project quota exceeded. You can create up to {$quota->quota} project(s).");
                }
            }
        }

        // Unique token oluştur (soft deleted projeler dahil kontrol et)
        do {
            $token = Str::random(32);
        } while (Project::withTrashed()->where('token', $token)->exists());

        return Project::create([
            'user_id' => $user->id,
            'name' => $dto->name,
            'token' => $token,
            'description' => $dto->description,
        ]);
    }
}
