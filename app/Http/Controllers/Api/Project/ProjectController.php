<?php

namespace App\Http\Controllers\Api\Project;

use App\Domain\Project\Actions\CreateProjectAction;
use App\Domain\Project\Actions\DeleteProjectAction;
use App\Domain\Project\Actions\ForceDeleteProjectAction;
use App\Domain\Project\Actions\RestoreProjectAction;
use App\Domain\Project\Actions\UpdateProjectAction;
use App\Domain\Project\DataTransferObjects\CreateProjectDTO;
use App\Domain\Project\DataTransferObjects\ProjectDTO;
use App\Domain\Project\DataTransferObjects\UpdateProjectDTO;
use App\Domain\Project\Models\Project;
use App\Http\Controllers\Controller;
use App\Http\Requests\Project\CreateProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class ProjectController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/projects",
     *     summary="Get User's Projects List",
     *     tags={"Projects"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="with_trashed",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="boolean", example=true),
     *         description="Include soft deleted projects"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of projects",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="string"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="token", type="string"),
     *                 @OA\Property(property="description", type="string", nullable=true),
     *                 @OA\Property(property="accessible", type="boolean", example=true),
     *                 @OA\Property(property="created_at", type="string"),
     *                 @OA\Property(property="updated_at", type="string"),
     *                 @OA\Property(property="deleted_at", type="string", nullable=true)
     *             )),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $withTrashed = $request->boolean('with_trashed', true);

        $query = Project::where('user_id', $request->user()->id);

        if ($withTrashed) {
            $query->withTrashed();
        }

        $query->orderByRaw('deleted_at IS NOT NULL, deleted_at ASC');
        
        $projects = $query->latest()->paginate(20);

        $projectsData = $projects->map(fn($project) => ProjectDTO::fromModel($project)->toArray());

        return response()->json([
            'status' => 'success',
            'data' => $projectsData->toArray(),
            'meta' => [
                'current_page' => $projects->currentPage(),
                'last_page' => $projects->lastPage(),
                'per_page' => $projects->perPage(),
                'total' => $projects->total(),
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/projects",
     *     summary="Create New Project",
     *     tags={"Projects"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="My Project"),
     *             @OA\Property(property="description", type="string", nullable=true, example="Project description")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Project created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="project", type="object")
     *             ),
     *             @OA\Property(property="message", type="string", example="Project created successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Quota exceeded",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Project quota exceeded. You can create up to 1 project(s).")
     *         )
     *     )
     * )
     */
    public function store(
        CreateProjectRequest $request,
        CreateProjectAction $action
    ): JsonResponse {
        try {
            $project = $action->execute(
                $request->user(),
                CreateProjectDTO::fromRequest($request->validated())
            );

            return response()->json([
                'status' => 'success',
                'data' => [
                    'project' => ProjectDTO::fromModel($project)->toArray(),
                ],
                'message' => 'Project created successfully.',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 403);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/projects/{id}",
     *     summary="Get Project Details",
     *     tags={"Projects"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Project details",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="project", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Project not found")
     * )
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $project = Project::withTrashed()
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        return response()->json([
            'status' => 'success',
            'data' => [
                'project' => ProjectDTO::fromModel($project)->toArray(),
            ],
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/projects/{id}",
     *     summary="Update Project",
     *     tags={"Projects"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Project Name"),
     *             @OA\Property(property="token", type="string", example="updated-token"),
     *             @OA\Property(property="description", type="string", nullable=true, example="Updated description")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Project updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="project", type="object")
     *             ),
     *             @OA\Property(property="message", type="string", example="Project updated successfully.")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Project not found")
     * )
     */
    public function update(
        UpdateProjectRequest $request,
        UpdateProjectAction $action,
        string $id
    ): JsonResponse {
        $project = Project::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->withTrashed()
            ->firstOrFail();

        // Eğer silinmişse güncelleme yapılamaz
        if ($project->trashed()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot update a deleted project.',
            ], 400);
        }

        $project = $action->execute(
            $project,
            UpdateProjectDTO::fromRequest($request->validated())
        );

        return response()->json([
            'status' => 'success',
            'data' => [
                'project' => ProjectDTO::fromModel($project)->toArray(),
            ],
            'message' => 'Project updated successfully.',
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/projects/{id}",
     *     summary="Delete Project",
     *     tags={"Projects"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Project deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Project deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Project not found")
     * )
     */
    public function destroy(
        Request $request,
        DeleteProjectAction $action,
        string $id
    ): JsonResponse {
        $project = Project::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->withTrashed()
            ->firstOrFail();

        // Eğer zaten silinmişse hata döndür
        if ($project->trashed()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Project is already deleted.',
            ], 400);
        }

        $action->execute($project, 'user_request', 'Project deleted by user');

        return response()->json([
            'status' => 'success',
            'message' => 'Project deleted successfully.',
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/projects/{id}/restore",
     *     summary="Restore Soft Deleted Project",
     *     tags={"Projects"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Project restored successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="project", type="object")
     *             ),
     *             @OA\Property(property="message", type="string", example="Project restored successfully.")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Project not found"),
     *     @OA\Response(response=400, description="Project is not deleted"),
     *     @OA\Response(
     *         response=403,
     *         description="Quota exceeded",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Cannot restore project. Project quota exceeded. You can have up to 1 active project(s).")
     *         )
     *     )
     * )
     */
    public function restore(
        Request $request,
        RestoreProjectAction $action,
        string $id
    ): JsonResponse {
        try {
            $project = Project::withTrashed()
                ->where('id', $id)
                ->where('user_id', $request->user()->id)
                ->firstOrFail();

            // Eğer zaten aktifse hata döndür
            if (!$project->trashed()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Project is already active.',
                ], 400);
            }

            $action->execute($project, 'user_request', 'Project restored by user');

            return response()->json([
                'status' => 'success',
                'data' => [
                    'project' => ProjectDTO::fromModel($project->fresh())->toArray(),
                ],
                'message' => 'Project restored successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 403);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/projects/{id}/force",
     *     summary="Permanently Delete Project",
     *     tags={"Projects"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Project permanently deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Project permanently deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Project not found")
     *     )
     * )
     */
    public function forceDelete(
        Request $request,
        ForceDeleteProjectAction $action,
        string $id
    ): JsonResponse {
        $project = Project::withTrashed()
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $action->execute($project, 'user_request', 'Project permanently deleted by user');

        return response()->json([
            'status' => 'success',
            'message' => 'Project permanently deleted successfully.',
        ]);
    }
}
