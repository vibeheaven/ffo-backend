<?php

namespace App\Http\Controllers\Api\Business;

use App\Domain\Business\Actions\CreateBusinessAction;
use App\Domain\Business\Actions\UpdateBusinessAction;
use App\Domain\Business\Models\Business;
use App\Domain\Project\Models\Project;
use App\Http\Controllers\Controller;
use App\Http\Requests\Business\CreateBusinessRequest;
use App\Http\Requests\Business\UpdateBusinessRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class BusinessController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/projects/{projectId}/business",
     *     summary="Get Business Profile",
     *     tags={"Business"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="projectId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Business profile",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="business", type="object")
     *             )
     *         )
     *     )
     * )
     */
    public function show(Request $request, string $projectId): JsonResponse
    {
        try {
            $project = Project::withTrashed()
                ->where('id', $projectId)
                ->where('user_id', $request->user()->id)
                ->firstOrFail();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Project not found or you do not have access to this project.',
            ], 404);
        }

        $business = $project->business()->withTrashed()->first();

        if (!$business) {
            return response()->json([
                'status' => 'error',
                'message' => 'Business profile not found.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'business' => $business->toArray(),
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/projects/{projectId}/business",
     *     summary="Create Business Profile",
     *     tags={"Business"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="projectId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="My Business")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Business created successfully"
     *     )
     *     )
     * )
     */
    public function store(
        CreateBusinessRequest $request,
        CreateBusinessAction $action,
        string $projectId
    ): JsonResponse {
        try {
            $project = Project::where('id', $projectId)
                ->where('user_id', $request->user()->id)
                ->firstOrFail();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Project not found or you do not have access to this project.',
            ], 404);
        }

        // Eğer zaten business varsa hata döndür
        if ($project->business()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Business profile already exists for this project.',
            ], 400);
        }

        $business = $action->execute(
            $project,
            \App\Domain\Business\DataTransferObjects\CreateBusinessDTO::fromRequest($request->validated())
        );

        return response()->json([
            'status' => 'success',
            'data' => [
                'business' => $business->toArray(),
            ],
            'message' => 'Business profile created successfully.',
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/projects/{projectId}/business",
     *     summary="Update Business Profile",
     *     tags={"Business"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="projectId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Business updated successfully"
     *     )
     *     )
     * )
     */
    public function update(
        UpdateBusinessRequest $request,
        UpdateBusinessAction $action,
        string $projectId
    ): JsonResponse {
        $project = Project::withTrashed()
            ->where('id', $projectId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $business = $project->business()->withTrashed()->firstOrFail();

        // Eğer silinmişse güncelleme yapılamaz
        if ($business->trashed()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot update a deleted business profile.',
            ], 400);
        }

        $business = $action->execute(
            $business,
            \App\Domain\Business\DataTransferObjects\UpdateBusinessDTO::fromRequest($request->validated())
        );

        return response()->json([
            'status' => 'success',
            'data' => [
                'business' => $business->toArray(),
            ],
            'message' => 'Business profile updated successfully.',
        ]);
    }
}
