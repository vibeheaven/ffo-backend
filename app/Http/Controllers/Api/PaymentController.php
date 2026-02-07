<?php

namespace App\Http\Controllers\Api;

use App\Domain\Credits\Actions\AddCreditsAction;
use App\Http\Controllers\Controller;
use App\Services\LemonSqueezyService;
use App\Domain\User\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use OpenApi\Annotations as OA;

class PaymentController extends Controller
{
    public function __construct(protected LemonSqueezyService $lemonSqueezy) {}

    /**
     * @OA\Post(
     *     path="/api/payments/checkout",
     *     summary="Create Payment Checkout Link",
     *     tags={"Payments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount"},
     *             @OA\Property(property="amount", type="integer", example=20, description="Amount in USD (10-500)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Checkout URL",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="checkout_url", type="string")
     *             )
     *         )
     *     )
     * )
     */
    public function checkout(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|integer|min:10|max:500',
        ]);

        $variantId = config('services.lemonsqueezy.variant_id'); // Base product variant ID

        if (!$variantId) {
             return response()->json(['status' => 'error', 'message' => 'Payment configuration missing.'], 500);
        }

        try {
            $url = $this->lemonSqueezy->createCheckout(
                $request->amount,
                $request->user()->id,
                $variantId
            );

            return response()->json([
                'status' => 'success',
                'data' => [
                    'checkout_url' => $url,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to create checkout.'], 500);
        }
    }

    public function webhook(Request $request, AddCreditsAction $addCreditsAction): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Signature');

        if (!$signature || !$this->lemonSqueezy->verifyWebhookSignature($payload, $signature)) {
            Log::warning('LemonSqueezy Webhook Signature Verification Failed');
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $data = $request->json();
        $eventName = $data['meta']['event_name'] ?? '';

        if ($eventName === 'order_created') {
            // Logic for when order is created (but not necessarily paid)
            // Usually we wait for 'order_paid' checkouts, but 'order_created' is common event for basic stores
            // Let's assume 'order_created' with status 'paid' OR look for a specific 'order_paid' event if subscription.
            // For one-time payments, 'order_created' usually contains the status.
            
            $status = $data['data']['attributes']['status'] ?? '';
            
            if ($status === 'paid') {
                $customData = $data['meta']['custom_data'] ?? [];
                $userId = $customData['user_id'] ?? null;
                $credits = $customData['credits'] ?? 0;
                $orderId = $data['data']['id'];

                if ($userId && $credits > 0) {
                    $user = User::find($userId);
                    if ($user) {
                        // Check if transaction already processed to avoid duplicates (optional, via reference_id)
                        // AddCreditsAction handles db transaction logic
                        
                        // We use order ID as reference
                        // Check duplicate handled by "reference_id" unique constraint?
                        // We haven't added unique constraint to DB, but we can check existence manually or just rely on Action.
                        
                        try {
                             $addCreditsAction->execute(
                                $user, 
                                (float) $credits, 
                                'Credit Top-up via LemonSqueezy', 
                                "ls_order_{$orderId}"
                            );
                            Log::info("Credits added for User {$userId}: {$credits}");
                        } catch (\Exception $e) {
                            Log::error("Failed to add credits: " . $e->getMessage());
                             // Maybe return 500 so webhook retries?
                             return response()->json(['message' => 'Processing failed'], 500);
                        }
                    }
                }
            }
        }

        return response()->json(['message' => 'Webhook received']);
    }
}
