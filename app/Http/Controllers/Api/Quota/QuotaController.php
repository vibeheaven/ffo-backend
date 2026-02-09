<?php

namespace App\Http\Controllers\Api\Quota;

use App\Domain\Quota\Actions\CreateDefaultQuotaAction;
use App\Domain\Quota\Actions\UpdateQuotaAction;
use App\Domain\Quota\DataTransferObjects\QuotaDTO;
use App\Domain\Quota\DataTransferObjects\UpdateQuotaDTO;
use App\Domain\Quota\Models\ProjectQuota;
use App\Domain\User\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Quota\UpdateQuotaRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class QuotaController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/quota",
     *     summary="Get User's Project Quota",
     *     tags={"Quota"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Quota information",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="quota", type="object",
     *                     @OA\Property(property="id", type="string"),
     *                     @OA\Property(property="quota", type="integer", example=1),
     *                     @OA\Property(property="created_at", type="string"),
     *                     @OA\Property(property="updated_at", type="string")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function show(
        Request $request,
        CreateDefaultQuotaAction $createDefaultQuotaAction
    ): JsonResponse {
        // Eğer quota yoksa default oluştur
        $quota = ProjectQuota::where('user_id', $request->user()->id)->first();

        if (!$quota) {
            $quota = $createDefaultQuotaAction->execute($request->user());
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'quota' => QuotaDTO::fromModel($quota)->toArray(),
            ],
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/quota",
     *     summary="Update User's Project Quota",
     *     tags={"Quota"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"quota"},
     *             @OA\Property(property="quota", type="integer", example=5, description="New quota value"),
     *             @OA\Property(property="user_id", type="integer", nullable=true, example=1, description="Target user ID (optional, defaults to current user. Only root users can update other users' quota)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized - Only root users can update quota",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Unauthorized. Only root users can update quota.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Quota updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="quota", type="object")
     *             ),
     *             @OA\Property(property="message", type="string", example="Quota updated successfully.")
     *         )
     *     )
     * )
     */
    public function update(
        UpdateQuotaRequest $request,
        UpdateQuotaAction $updateQuotaAction,
        CreateDefaultQuotaAction $createDefaultQuotaAction
    ): JsonResponse {
        // Sadece root kullanıcılar quota güncelleyebilir
        $currentUserId = $request->user()->id;
        $rootUsers = config('app.root', []);

        if (!in_array($currentUserId, $rootUsers)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Only root users can update quota.',
            ], 403);
        }

        // Eğer user_id belirtilmişse o kullanıcının quota'sını güncelle, yoksa kendi quota'sını
        $targetUserId = $request->validated()['user_id'] ?? $currentUserId;
        $targetUser = User::findOrFail($targetUserId);

        // Eğer quota yoksa önce oluştur
        $quota = ProjectQuota::where('user_id', $targetUserId)->first();

        if (!$quota) {
            $quota = $createDefaultQuotaAction->execute($targetUser);
        }

        $quota = $updateQuotaAction->execute(
            $quota,
            UpdateQuotaDTO::fromRequest($request->validated())
        );

        return response()->json([
            'status' => 'success',
            'data' => [
                'quota' => QuotaDTO::fromModel($quota)->toArray(),
            ],
            'message' => 'Quota updated successfully.',
        ]);
    }
}
