<?php

namespace App\Domain\Business\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BrandIdentity extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'business_id',
        'primary_color',
        'secondary_color',
        'accent_color',
        'font_family',
        'font_secondary',
        'visual_style_notes',
        'intro_video_path',
        'outro_video_path',
        'watermark_logo_path',
        'jingle_audio_path',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
