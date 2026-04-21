<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class BaseApiController extends Controller
{
    /**
     * Send success response.
     */
    public function sendResponse($result, $message = 'Success'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $result,
            'message' => $message,
        ], 200);
    }

    /**
     * Send error response.
     */
    public function sendError($error, $errorMessages = [], $code = 404): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];

        if (! empty($errorMessages)) {
            $response['errors'] = $errorMessages;
        }

        return response()->json($response, $code);
    }
}
