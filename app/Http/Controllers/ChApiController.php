<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ChApiController extends Controller
{
    public function lookup(Request $request): JsonResponse
    {
        return response()->json([
            'ok'   => true,
            'name' => (string) $request->query('name'),
        ]);
    }
}
