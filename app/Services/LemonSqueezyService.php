<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class LemonSqueezyService
{
    protected string $apiKey;
    protected string $storeId;
    protected string $webhookSecret;

    public function __construct()
    {
        $this->apiKey = config('services.lemonsqueezy.key');
        $this->storeId = config('services.lemonsqueezy.store_id');
        $this->webhookSecret = config('services.lemonsqueezy.webhook_secret');
    }

    public function createCheckout(int $amount, int $userId, string $variantId): string
    {
        // LemonSqueezy API to create a checkout
        // We use 'custom_price' to allow dynamic credit loading ($10-$500)
        // Amount is likely in cents for the API, so multiply by 100
        
        $response = Http::withToken($this->apiKey)
            ->accept('application/vnd.api+json')
            ->contentType('application/vnd.api+json')
            ->post('https://api.lemonsqueezy.com/v1/checkouts', [
                'data' => [
                    'type' => 'checkouts',
                    'attributes' => [
                        'checkout_data' => [
                            'custom' => [
                                'user_id' => (string) $userId,
                                'credits' => $amount, // Store formatted amount or credits in custom data
                            ],
                        ],
                        'custom_price' => $amount * 100, // Amount in cents
                        'product_options' => [
                            'enabled_variants' => [$variantId], // We need a base product/variant ID
                        ],
                    ],
                    'relationships' => [
                        'store' => [
                            'data' => [
                                'type' => 'stores',
                                'id' => $this->storeId,
                            ],
                        ],
                        'variant' => [
                            'data' => [
                                'type' => 'variants',
                                'id' => $variantId,
                            ],
                        ],
                    ],
                ],
            ]);

        if ($response->failed()) {
            throw new Exception('LemonSqueezy Checkout Creation Failed: ' . $response->body());
        }

        return $response->json('data.attributes.url');
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $hash = hash_hmac('sha256', $payload, $this->webhookSecret);
        return hash_equals($hash, $signature);
    }
}
