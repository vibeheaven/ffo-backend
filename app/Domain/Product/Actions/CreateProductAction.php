<?php

namespace App\Domain\Product\Actions;

use App\Domain\Business\Models\Business;
use App\Domain\Product\DataTransferObjects\CreateProductDTO;
use App\Domain\Product\Models\Product;
use Illuminate\Support\Str;

class CreateProductAction
{
    public function execute(Business $business, CreateProductDTO $dto): Product
    {
        // Slug oluştur
        $slug = Str::slug($dto->name);
        $originalSlug = $slug;
        $counter = 1;

        // Unique slug kontrolü
        while (Product::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return Product::create([
            'business_id' => $business->id,
            'slug' => $slug,
            ...$dto->toArray(),
        ]);
    }
}
