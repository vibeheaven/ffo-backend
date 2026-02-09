<?php

namespace App\Domain\Product\Actions;

use App\Domain\Product\Models\Product;
use App\Domain\Product\Models\ProductMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CreateProductMediaAction
{
    public function execute(Product $product, UploadedFile $file, string $type = 'image'): ProductMedia
    {
        // Önceki görseli kontrol et ve sil
        $existingMedia = ProductMedia::where('product_id', $product->id)
            ->whereNull('deleted_at')
            ->first();

        if ($existingMedia) {
            // Önceki görselin dosyasını storage'dan sil
            if (Storage::disk('public')->exists($existingMedia->file_path)) {
                Storage::disk('public')->delete($existingMedia->file_path);
            }
            
            // Önceki görseli soft delete yap
            $existingMedia->delete();
        }

        // Dosya adını oluştur
        $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        
        // Dosyayı storage'a kaydet
        $storedPath = $file->storeAs('products/' . $product->id . '/' . $type, $fileName, 'public');
        
        // ProductMedia kaydı oluştur
        return ProductMedia::create([
            'product_id' => $product->id,
            'type' => $type,
            'file_path' => $storedPath,
        ]);
    }
}
