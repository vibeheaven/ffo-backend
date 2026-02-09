<?php

namespace App\Domain\Project\Traits;

use App\Domain\Business\Models\Business;

trait CascadeRestore
{
    /**
     * Boot the trait and add event listeners for cascade restore
     */
    public static function bootCascadeRestore(): void
    {
        static::restored(function ($model) {
            $model->cascadeRestore();
        });
    }

    /**
     * Restore all related soft-deleted records
     */
    public function cascadeRestore(): void
    {
        // Business ve tüm alt kayıtları restore et
        $business = $this->business()->withTrashed()->first();
        if ($business && $business->trashed()) {
            $business->restore();
            $this->restoreBusinessRelations($business);
        }
    }

    /**
     * Restore all business-related records
     */
    protected function restoreBusinessRelations(Business $business): void
    {
        // Brand Identity
        $brandIdentity = $business->brandIdentity()->withTrashed()->first();
        if ($brandIdentity && $brandIdentity->trashed()) {
            $brandIdentity->restore();
        }
        
        // Products
        $business->products()->withTrashed()->get()->each(function ($product) {
            if ($product->trashed()) {
                $product->restore();
            }
            $product->media()->withTrashed()->get()->each(fn($media) => $media->restore());
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
        $business->audiencePersonas()->withTrashed()->get()->each(fn($persona) => $persona->restore());
        
        // Campaigns
        $business->campaigns()->withTrashed()->get()->each(function ($campaign) {
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
                    $content->performances()->withTrashed()->get()->each(fn($perf) => $perf->restore());
                });
            });
        });
        
        // Social Accounts
        $business->socialAccounts()->withTrashed()->get()->each(fn($account) => $account->restore());
    }
}
