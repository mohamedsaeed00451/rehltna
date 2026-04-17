<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;

trait ResponseTrait
{
    public function responseMessage($code, $message = null, $data = null): JsonResponse
    {
        return response()->json([
            'code' => $code,
            'message' => $message,
            'data' => $data
        ]);
    }
}
