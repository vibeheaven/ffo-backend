<?php

namespace App\Domain\Product\DataTransferObjects;

use App\Domain\Product\Models\ProductMedia;

class ProductMediaDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $product_id,
        public readonly string $type,
        public readonly string $file_path,
        public readonly string $url,
        public readonly bool $accessible,
        public readonly ?string $deleted_at,
        public readonly string $created_at,
        public readonly string $updated_at,
    ) {}

    public static function fromModel(ProductMedia $media): self
    {
        return new self(
            id: $media->id,
            product_id: $media->product_id,
            type: $media->type,
            file_path: $media->file_path,
            url: \Illuminate\Support\Facades\Storage::url($media->file_path),
            accessible: $media->deleted_at === null,
            deleted_at: $media->deleted_at?->toIso8601String(),
            created_at: $media->created_at->toIso8601String(),
            updated_at: $media->updated_at->toIso8601String(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'type' => $this->type,
            'file_path' => $this->file_path,
            'url' => $this->url,
            'accessible' => $this->accessible,
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
