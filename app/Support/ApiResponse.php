<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

/**
 * Helper for building the consistent { status, message, data } envelope
 * used by every endpoint in the API.
 */
class ApiResponse
{
    /**
     * Build a successful JSON response.
     */
    public static function success(
        mixed $data = null,
        string $message = 'OK',
        int $status = 200,
        array $meta = []
    ): JsonResponse {
        $payload = [
            'status' => true,
            'message' => $message,
            'data' => $data,
        ];

        if ($meta !== []) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $status);
    }

    /**
     * Build a failed JSON response.
     */
    public static function error(
        string $message = 'Something went wrong',
        int $status = 400,
        array $errors = []
    ): JsonResponse {
        $payload = [
            'status' => false,
            'message' => $message,
        ];

        if ($errors !== []) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }
}
