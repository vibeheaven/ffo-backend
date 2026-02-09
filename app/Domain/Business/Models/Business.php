<?php

namespace App\Domain\Business\Models;

use App\Domain\Audience\Models\AudiencePersona;
use App\Domain\Campaign\Models\Campaign;
use App\Domain\Product\Models\Product;
use App\Domain\Project\Models\Project;
use App\Domain\Social\Models\SocialAccount;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Business extends Model
{
    use HasUuids, SoftDeletes;

    protected static function booted(): void
    {
        static::deleted(function ($business) {
            if ($business->isForceDeleting()) {
                return; // Hard delete - cascade zaten çalışıyor
            }
            
            // Soft delete - cascade soft delete
            $business->cascadeSoftDelete();
        });

        static::restored(function ($business) {
            $business->cascadeRestore();
        });
    }

    protected $fillable = [
        'project_id',
        'name',
        'legal_name',
        'sector',
        'sub_sector',
        'phone',
        'whatsapp_number',
        'website',
        'country',
        'city',
        'address',
        'logo_path',
        'brand_story',
        'brand_tone',
        'brand_voice_rules',
        'forbidden_words',
        'competitor_names',
    ];

    protected $casts = [
        'forbidden_words' => 'array',
        'competitor_names' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function brandIdentity(): HasOne
    {
        return $this->hasOne(BrandIdentity::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function audiencePersonas(): HasMany
    {
        return $this->hasMany(AudiencePersona::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    /**
     * Cascade soft delete all related records
     */
    protected function cascadeSoftDelete(): void
    {
        // Brand Identity
        $brandIdentity = $this->brandIdentity()->first();
        if ($brandIdentity) {
            $brandIdentity->delete();
        }
        
        // Products
        $this->products()->get()->each(function ($product) {
            $product->delete();
            $product->media()->get()->each(fn($media) => $media->delete());
            $physicalDetails = $product->physicalDetails()->first();
            if ($physicalDetails) {
                $physicalDetails->delete();
            }
            $digitalDetails = $product->digitalDetails()->first();
            if ($digitalDetails) {
                $digitalDetails->delete();
            }
        });
        
        // Audience Personas
        $this->audiencePersonas()->get()->each(fn($persona) => $persona->delete());
        
        // Campaigns
        $this->campaigns()->get()->each(function ($campaign) {
            $campaign->delete();
            $creativeBrief = $campaign->creativeBrief()->first();
            if ($creativeBrief) {
                $creativeBrief->delete();
            }
            $campaign->contentQueues()->get()->each(function ($queue) {
                $queue->delete();
                $queue->generatedContents()->get()->each(function ($content) {
                    $content->delete();
                    $content->performances()->get()->each(fn($perf) => $perf->delete());
                });
            });
        });
        
        // Social Accounts
        $this->socialAccounts()->get()->each(fn($account) => $account->delete());
    }

    /**
     * Cascade restore all related records
     */
    protected function cascadeRestore(): void
    {
        // Brand Identity
        $brandIdentity = $this->brandIdentity()->withTrashed()->first();
        if ($brandIdentity && $brandIdentity->trashed()) {
            $brandIdentity->restore();
        }
        
        // Products
        $this->products()->withTrashed()->get()->each(function ($product) {
            if ($product->trashed()) {
                $product->restore();
            }
            $product->media()->withTrashed()->get()->each(function ($media) {
                if ($media->trashed()) {
                    $media->restore();
                }
            });
            $physicalDetails = $product->physicalDetails()->withTrashed()->first();
            if ($physicalDetails && $physicalDetails->trashed()) {
                $physicalDetails->restore();
            }
            $digitalDetails = $product->digitalDetails()->withTrashed()->first();
            if ($digitalDetails && $digitalDetails->trashed()) {
                $digitalDetails->restore();
            }
        });
        
        // Audience Personas
        $this->audiencePersonas()->withTrashed()->get()->each(function ($persona) {
            if ($persona->trashed()) {
                $persona->restore();
            }
        });
        
        // Campaigns
        $this->campaigns()->withTrashed()->get()->each(function ($campaign) {
            if ($campaign->trashed()) {
                $campaign->restore();
            }
            $creativeBrief = $campaign->creativeBrief()->withTrashed()->first();
            if ($creativeBrief && $creativeBrief->trashed()) {
                $creativeBrief->restore();
            }
            $campaign->contentQueues()->withTrashed()->get()->each(function ($queue) {
                if ($queue->trashed()) {
                    $queue->restore();
                }
                $queue->generatedContents()->withTrashed()->get()->each(function ($content) {
                    if ($content->trashed()) {
                        $content->restore();
                    }
                    $content->performances()->withTrashed()->get()->each(function ($perf) {
                        if ($perf->trashed()) {
                            $perf->restore();
                        }
                    });
                });
            });
        });
        
        // Social Accounts
        $this->socialAccounts()->withTrashed()->get()->each(function ($account) {
            if ($account->trashed()) {
                $account->restore();
            }
        });
    }
}
