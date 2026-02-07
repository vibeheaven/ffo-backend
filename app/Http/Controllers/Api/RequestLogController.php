<?php

namespace App\Http\Controllers\Api;

use App\Domain\Logging\Models\RequestLog;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class RequestLogController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/user/request-history",
     *     summary="Get User Request History",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of request logs",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="string"),
     *                 @OA\Property(property="method", type="string"),
     *                 @OA\Property(property="endpoint", type="string"),
     *                 @OA\Property(property="status_code", type="integer"),
     *                 @OA\Property(property="ip_address", type="string"),
     *                 @OA\Property(property="duration_ms", type="integer"),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             )),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $logs = RequestLog::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $logs->items(),
            'meta' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
            ],
        ]);
    }
}
