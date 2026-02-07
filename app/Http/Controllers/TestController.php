<?php

namespace App\Http\Controllers;

use OpenApi\Annotations as OA;

class TestController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="List Users",
     *     @OA\Response(response=200, description="OK")
     * )
     */
    public function index()
    {
        //
    }
}
