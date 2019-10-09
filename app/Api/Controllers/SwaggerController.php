<?php

namespace App\Api\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * @OA\Info(
 *     title="Qu Dao API",
 *     version="1.0"
 * )
 */

class SwaggerController extends Controller
{
    public function doc()
    {
        $swagger = \OpenApi\scan(app_path('Api/Controllers/'));
        return response()->json($swagger);
    }
}
