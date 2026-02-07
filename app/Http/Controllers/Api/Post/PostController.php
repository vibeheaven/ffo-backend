<?php

namespace App\Http\Controllers\Api\Post;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use OpenApi\Annotations as OA;

class PostController extends Controller
{
    use ApiResponse;

    /**
     * @OA\Get(
     *      path="/api/posts",
     *      operationId="getPostsList",
     *      tags={"Posts"},
     *      summary="Get list of Posts",
     *      description="Returns list of Posts",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       )
     *     )
     */
    public function index()
    {
        return $this->success([], 'List of Posts');
    }
}
