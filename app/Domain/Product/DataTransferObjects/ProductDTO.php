<?php

namespace App\Domain\Product\DataTransferObjects;

use App\Domain\Product\Models\Product;

class ProductDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $business_id,
        public readonly string $name,
        public readonly string $slug,
        public readonly string $type,
        public readonly ?string $category,
        public readonly ?string $short_description,
        public readonly ?string $long_description,
        public readonly ?float $price,
        public readonly ?float $discount_price,
        public readonly string $currency,
        public readonly ?string $sku,
        public readonly string $stock_status,
        public readonly ?string $product_url,
        public readonly ?array $key_benefits,
        public readonly ?array $technical_specs,
        public readonly ?array $target_persona_tags,
        public readonly ?string $problem_it_solves,
        public readonly ?array $objections,
        public readonly ?array $faqs,
        public readonly bool $accessible,
        public readonly ?string $deleted_at,
        public readonly string $created_at,
        public readonly string $updated_at,
    ) {}

    public static function fromModel(Product $product): self
    {
        return new self(
            id: $product->id,
            business_id: $product->business_id,
            name: $product->name,
            slug: $product->slug,
            type: $product->type,
            category: $product->category,
            short_description: $product->short_description,
            long_description: $product->long_description,
            price: $product->price,
            discount_price: $product->discount_price,
            currency: $product->currency,
            sku: $product->sku,
            stock_status: $product->stock_status,
            product_url: $product->product_url,
            key_benefits: $product->key_benefits,
            technical_specs: $product->technical_specs,
            target_persona_tags: $product->target_persona_tags,
            problem_it_solves: $product->problem_it_solves,
            objections: $product->objections,
            faqs: $product->faqs,
            accessible: $product->deleted_at === null,
            deleted_at: $product->deleted_at?->toIso8601String(),
            created_at: $product->created_at->toIso8601String(),
            updated_at: $product->updated_at->toIso8601String(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->business_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type,
            'category' => $this->category,
            'short_description' => $this->short_description,
            'long_description' => $this->long_description,
            'price' => $this->price,
            'discount_price' => $this->discount_price,
            'currency' => $this->currency,
            'sku' => $this->sku,
            'stock_status' => $this->stock_status,
            'product_url' => $this->product_url,
            'key_benefits' => $this->key_benefits,
            'technical_specs' => $this->technical_specs,
            'target_persona_tags' => $this->target_persona_tags,
            'problem_it_solves' => $this->problem_it_solves,
            'objections' => $this->objections,
            'faqs' => $this->faqs,
            'accessible' => $this->accessible,
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
