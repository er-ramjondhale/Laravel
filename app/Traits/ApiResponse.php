<?php

namespace App\Traits;

trait ApiResponse
{
    public function successResponse($message, $data = [], $status = 200)
    {
        return response()->json(['status' => true, 'message' => $message, 'data' => $data], $status);
    }

    public function errorResponse($message, $data = [], $status = 200)
    {
        return response()->json(['status' => false, 'message' => $message, 'data' => $data], $status);
    }
}
