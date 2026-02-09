<?php

namespace App\Domain\Business\DataTransferObjects;

class UpdateBusinessDTO
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $legal_name = null,
        public readonly ?string $sector = null,
        public readonly ?string $sub_sector = null,
        public readonly ?string $phone = null,
        public readonly ?string $whatsapp_number = null,
        public readonly ?string $website = null,
        public readonly ?string $country = null,
        public readonly ?string $city = null,
        public readonly ?string $address = null,
        public readonly ?string $logo_path = null,
        public readonly ?string $brand_story = null,
        public readonly ?string $brand_tone = null,
        public readonly ?string $brand_voice_rules = null,
        public readonly ?array $forbidden_words = null,
        public readonly ?array $competitor_names = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            legal_name: $data['legal_name'] ?? null,
            sector: $data['sector'] ?? null,
            sub_sector: $data['sub_sector'] ?? null,
            phone: $data['phone'] ?? null,
            whatsapp_number: $data['whatsapp_number'] ?? null,
            website: $data['website'] ?? null,
            country: $data['country'] ?? null,
            city: $data['city'] ?? null,
            address: $data['address'] ?? null,
            logo_path: $data['logo_path'] ?? null,
            brand_story: $data['brand_story'] ?? null,
            brand_tone: $data['brand_tone'] ?? null,
            brand_voice_rules: $data['brand_voice_rules'] ?? null,
            forbidden_words: $data['forbidden_words'] ?? null,
            competitor_names: $data['competitor_names'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'legal_name' => $this->legal_name,
            'sector' => $this->sector,
            'sub_sector' => $this->sub_sector,
            'phone' => $this->phone,
            'whatsapp_number' => $this->whatsapp_number,
            'website' => $this->website,
            'country' => $this->country,
            'city' => $this->city,
            'address' => $this->address,
            'logo_path' => $this->logo_path,
            'brand_story' => $this->brand_story,
            'brand_tone' => $this->brand_tone,
            'brand_voice_rules' => $this->brand_voice_rules,
            'forbidden_words' => $this->forbidden_words,
            'competitor_names' => $this->competitor_names,
        ], fn($value) => $value !== null);
    }
}
