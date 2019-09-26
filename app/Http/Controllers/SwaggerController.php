<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * @OA\Info(
 *     title="My First API",
 *     version="1.0"
 * )
 */

class SwaggerController extends Controller
{
    public function doc()
    {
        $swagger = \OpenApi\scan(app_path('Http/Controllers/'));
        return response()->json($swagger);
    }

    /**
     * @OA\Get(
     *     path="/api/swagger/get-mydata",
     *     @OA\Response(response="200", description="An example resource")
     * )
     */
    public function getMyData()
    {
        //todo 待实现
    }
}
