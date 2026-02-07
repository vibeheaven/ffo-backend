<?php

namespace App\Http\Controllers\Api;

use App\Domain\Credits\Models\CreditTransaction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class CreditController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/credits/balance",
     *     summary="Get Current Credit Balance",
     *     tags={"Credits"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Current balance",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="credits", type="number", format="float", example=50.00)
     *             )
     *         )
     *     )
     * )
     */
    public function balance(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'credits' => (float) $request->user()->credits,
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/credits/history",
     *     summary="Get Credit Transaction History",
     *     tags={"Credits"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Transaction history",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="string"),
     *                 @OA\Property(property="amount", type="number"),
     *                 @OA\Property(property="type", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="created_at", type="string")
     *             )),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     */
    public function history(Request $request): JsonResponse
    {
        $transactions = CreditTransaction::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $transactions->items(),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }
}
