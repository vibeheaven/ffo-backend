<?php

namespace App\Domain\Product\Actions;

use App\Domain\Product\Models\ProductMedia;
use Illuminate\Support\Facades\Storage;

class DeleteProductMediaAction
{
    public function execute(ProductMedia $media): bool
    {
        // Dosyayı storage'dan sil
        if (Storage::disk('public')->exists($media->file_path)) {
            Storage::disk('public')->delete($media->file_path);
        }
        
        // Soft delete
        return $media->delete();
    }
}
