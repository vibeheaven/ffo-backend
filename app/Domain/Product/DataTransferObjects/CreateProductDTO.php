<?php

namespace App\Domain\Product\DataTransferObjects;

class CreateProductDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $type,
        public readonly ?string $category = null,
        public readonly ?string $short_description = null,
        public readonly ?string $long_description = null,
        public readonly ?float $price = null,
        public readonly ?float $discount_price = null,
        public readonly ?string $currency = 'USD',
        public readonly ?string $sku = null,
        public readonly ?string $stock_status = 'in_stock',
        public readonly ?string $product_url = null,
        public readonly ?array $key_benefits = null,
        public readonly ?array $technical_specs = null,
        public readonly ?array $target_persona_tags = null,
        public readonly ?string $problem_it_solves = null,
        public readonly ?array $objections = null,
        public readonly ?array $faqs = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'],
            type: $data['type'],
            category: $data['category'] ?? null,
            short_description: $data['short_description'] ?? null,
            long_description: $data['long_description'] ?? null,
            price: isset($data['price']) ? (float) $data['price'] : null,
            discount_price: isset($data['discount_price']) ? (float) $data['discount_price'] : null,
            currency: $data['currency'] ?? 'USD',
            sku: $data['sku'] ?? null,
            stock_status: $data['stock_status'] ?? 'in_stock',
            product_url: $data['product_url'] ?? null,
            key_benefits: $data['key_benefits'] ?? null,
            technical_specs: $data['technical_specs'] ?? null,
            target_persona_tags: $data['target_persona_tags'] ?? null,
            problem_it_solves: $data['problem_it_solves'] ?? null,
            objections: $data['objections'] ?? null,
            faqs: $data['faqs'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
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
        ], fn($value) => $value !== null);
    }
}
