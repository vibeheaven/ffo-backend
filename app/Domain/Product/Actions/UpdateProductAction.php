<?php

namespace App\Domain\Product\Actions;

use App\Domain\Product\DataTransferObjects\UpdateProductDTO;
use App\Domain\Product\Models\Product;
use Illuminate\Support\Str;

class UpdateProductAction
{
    public function execute(Product $product, UpdateProductDTO $dto): Product
    {
        $updateData = $dto->toArray();

        // Eğer name değiştiyse slug'ı da güncelle
        if (isset($updateData['name']) && $updateData['name'] !== $product->name) {
            $slug = Str::slug($updateData['name']);
            $originalSlug = $slug;
            $counter = 1;

            // Unique slug kontrolü (mevcut product hariç)
            while (Product::where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            $updateData['slug'] = $slug;
        }

        $product->update($updateData);
        
        return $product->fresh();
    }
}
