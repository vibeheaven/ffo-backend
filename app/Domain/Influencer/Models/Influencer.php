<?php

namespace App\Domain\Influencer\Models;

use App\Domain\Project\Models\Project;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Influencer extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'project_id',
        'name',
        'social_media_name',
        'face_description',
        'body_description',
        'character_description',
        'country_ethnicity',
        'storage_folder',
        'face_images',
        'body_images',
    ];

    protected $casts = [
        'face_images' => 'array',
        'body_images' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($influencer) {
            // ID oluşturulunca storage folder'ı ayarla
            if (!$influencer->storage_folder) {
                $influencer->storage_folder = 'influencers/' . $influencer->id;
            }
        });

        static::deleting(function ($influencer) {
            if ($influencer->isForceDeleting()) {
                // Hard delete - resimleri sil
                if ($influencer->storage_folder) {
                    Storage::deleteDirectory($influencer->storage_folder);
                }
            }
        });
    }

    /**
     * Project ilişkisi
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Tüm yüz resimlerinin URL'lerini al
     */
    public function getFaceImageUrlsAttribute(): array
    {
        if (!$this->face_images) {
            return [];
        }

        return array_map(function ($filename) {
            return Storage::url($this->storage_folder . '/face/' . $filename);
        }, $this->face_images);
    }

    /**
     * Tüm fizik resimlerinin URL'lerini al
     */
    public function getBodyImageUrlsAttribute(): array
    {
        if (!$this->body_images) {
            return [];
        }

        return array_map(function ($filename) {
            return Storage::url($this->storage_folder . '/body/' . $filename);
        }, $this->body_images);
    }
}
