<?php

namespace App\Http\Controllers\Api\Product;

use App\Domain\Business\Models\Business;
use App\Domain\Product\Actions\CreateProductAction;
use App\Domain\Product\Actions\CreateProductMediaAction;
use App\Domain\Product\Actions\DeleteProductMediaAction;
use App\Domain\Product\Actions\UpdateProductAction;
use App\Domain\Product\DataTransferObjects\ProductDTO;
use App\Domain\Product\DataTransferObjects\ProductMediaDTO;
use App\Domain\Product\Models\Product;
use App\Domain\Product\Models\ProductMedia;
use App\Domain\Project\Models\Project;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\CreateProductMediaRequest;
use App\Http\Requests\Product\CreateProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/projects/{projectId}/business/products",
     *     summary="Get Products List",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="projectId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="with_trashed",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="boolean", example=true),
     *         description="Include soft deleted products"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of products"
     *     )
     * )
     */
    public function index(Request $request, string $projectId): JsonResponse
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
                'message' => 'Business profile not found for this project.',
            ], 404);
        }

        $withTrashed = $request->boolean('with_trashed', false);
        
        $query = $business->products();
        if ($withTrashed) {
            $query->withTrashed();
        }

        $products = $query->get()->map(function ($product) {
            return ProductDTO::fromModel($product)->toArray();
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'products' => $products,
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/projects/{projectId}/business/products/{productId}",
     *     summary="Get Product Details",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="projectId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="productId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product details"
     *     )
     * )
     */
    public function show(Request $request, string $projectId, string $productId): JsonResponse
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
                'message' => 'Business profile not found for this project.',
            ], 404);
        }

        $product = Product::withTrashed()
            ->where('id', $productId)
            ->where('business_id', $business->id)
            ->firstOrFail();

        return response()->json([
            'status' => 'success',
            'data' => [
                'product' => ProductDTO::fromModel($product)->toArray(),
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/projects/{projectId}/business/products",
     *     summary="Create Product",
     *     tags={"Products"},
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
     *             required={"name", "type"},
     *             @OA\Property(property="name", type="string", example="My Product"),
     *             @OA\Property(property="type", type="string", example="physical")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product created successfully"
     *     )
     * )
     */
    public function store(
        CreateProductRequest $request,
        CreateProductAction $action,
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

        $business = $project->business()->first();

        if (!$business) {
            return response()->json([
                'status' => 'error',
                'message' => 'Business profile not found for this project. Please create a business profile first.',
            ], 404);
        }

        $product = $action->execute(
            $business,
            \App\Domain\Product\DataTransferObjects\CreateProductDTO::fromRequest($request->validated())
        );

        return response()->json([
            'status' => 'success',
            'data' => [
                'product' => ProductDTO::fromModel($product)->toArray(),
            ],
            'message' => 'Product created successfully.',
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/projects/{projectId}/business/products/{productId}",
     *     summary="Update Product",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="projectId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="productId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product updated successfully"
     *     )
     * )
     */
    public function update(
        UpdateProductRequest $request,
        UpdateProductAction $action,
        string $projectId,
        string $productId
    ): JsonResponse {
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
                'message' => 'Business profile not found for this project.',
            ], 404);
        }

        $product = Product::withTrashed()
            ->where('id', $productId)
            ->where('business_id', $business->id)
            ->firstOrFail();

        // Eğer silinmişse güncelleme yapılamaz
        if ($product->trashed()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot update a deleted product.',
            ], 400);
        }

        $product = $action->execute(
            $product,
            \App\Domain\Product\DataTransferObjects\UpdateProductDTO::fromRequest($request->validated())
        );

        return response()->json([
            'status' => 'success',
            'data' => [
                'product' => ProductDTO::fromModel($product)->toArray(),
            ],
            'message' => 'Product updated successfully.',
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/projects/{projectId}/business/products/{productId}",
     *     summary="Delete Product",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="projectId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="productId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product deleted successfully"
     *     )
     * )
     */
    public function destroy(Request $request, string $projectId, string $productId): JsonResponse
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
                'message' => 'Business profile not found for this project.',
            ], 404);
        }

        $product = Product::withTrashed()
            ->where('id', $productId)
            ->where('business_id', $business->id)
            ->firstOrFail();

        // Eğer zaten silinmişse hata döndür
        if ($product->trashed()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product is already deleted.',
            ], 400);
        }

        $product->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Product deleted successfully.',
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/projects/{projectId}/business/products/{productId}/media",
     *     summary="Get Product Media",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="projectId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="productId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="with_trashed",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="boolean", example=true),
     *         description="Include soft deleted media"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product media"
     *     )
     * )
     */
    public function mediaIndex(Request $request, string $projectId, string $productId): JsonResponse
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
                'message' => 'Business profile not found for this project.',
            ], 404);
        }

        $product = Product::withTrashed()
            ->where('id', $productId)
            ->where('business_id', $business->id)
            ->firstOrFail();

        $withTrashed = $request->boolean('with_trashed', false);
        
        $query = $product->media();
        if ($withTrashed) {
            $query->withTrashed();
        }

        // Her ürün için sadece 1 görsel olduğu için tek kayıt döndür
        $media = $query->latest()->first();

        if (!$media) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'media' => null,
                ],
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'media' => ProductMediaDTO::fromModel($media)->toArray(),
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/projects/{projectId}/business/products/{productId}/media",
     *     summary="Upload Product Media (Replaces existing media if any)",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="projectId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="productId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"file"},
     *                 @OA\Property(property="file", type="string", format="binary"),
     *                 @OA\Property(property="type", type="string", example="image")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Media uploaded successfully. Previous media is automatically deleted."
     *     )
     * )
     */
    public function mediaStore(
        CreateProductMediaRequest $request,
        CreateProductMediaAction $action,
        string $projectId,
        string $productId
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

        $business = $project->business()->first();

        if (!$business) {
            return response()->json([
                'status' => 'error',
                'message' => 'Business profile not found for this project.',
            ], 404);
        }

        $product = Product::where('id', $productId)
            ->where('business_id', $business->id)
            ->firstOrFail();

        // Eğer silinmişse görsel eklenemez
        if ($product->trashed()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot add media to a deleted product.',
            ], 400);
        }

        // Dosya tipini belirle (image/video)
        $file = $request->file('file');
        $type = $request->input('type');
        
        if (!$type) {
            // MIME type'a göre otomatik belirle
            $mimeType = $file->getMimeType();
            if (str_starts_with($mimeType, 'image/')) {
                $type = 'image';
            } elseif (str_starts_with($mimeType, 'video/')) {
                $type = 'video';
            } else {
                $type = 'manual';
            }
        }

        // Action otomatik olarak önceki görseli silecek
        $media = $action->execute($product, $file, $type);

        return response()->json([
            'status' => 'success',
            'data' => [
                'media' => ProductMediaDTO::fromModel($media)->toArray(),
            ],
            'message' => 'Media uploaded successfully. Previous media has been deleted.',
        ], 201);
    }

    /**
     * @OA\Delete(
     *     path="/api/projects/{projectId}/business/products/{productId}/media/{mediaId}",
     *     summary="Delete Product Media",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="projectId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="productId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="mediaId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Media deleted successfully"
     *     )
     * )
     */
    public function mediaDestroy(
        Request $request,
        DeleteProductMediaAction $action,
        string $projectId,
        string $productId,
        string $mediaId
    ): JsonResponse {
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
                'message' => 'Business profile not found for this project.',
            ], 404);
        }

        $product = Product::withTrashed()
            ->where('id', $productId)
            ->where('business_id', $business->id)
            ->firstOrFail();

        $media = ProductMedia::withTrashed()
            ->where('id', $mediaId)
            ->where('product_id', $product->id)
            ->firstOrFail();

        // Eğer zaten silinmişse hata döndür
        if ($media->trashed()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Media is already deleted.',
            ], 400);
        }

        $action->execute($media);

        return response()->json([
            'status' => 'success',
            'message' => 'Media deleted successfully.',
        ]);
    }
}
