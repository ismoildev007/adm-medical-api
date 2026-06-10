<?php

namespace App\Traits;

trait ApiResponse
{
    /**
     * Build success response
     */
    public function successResponse($data = null, $message = 'Success', $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $code, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Build error response
     */
    public function errorResponse($message, $errorCode = 'SERVER_ERROR', $statusCode = 400, $errors = [])
    {
        $response = [
            'success' => false,
            'message' => $message,
            'error_code' => $errorCode,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode, [], JSON_UNESCAPED_UNICODE);
    }
}
