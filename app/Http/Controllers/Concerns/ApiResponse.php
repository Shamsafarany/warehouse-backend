<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

trait ApiResponse
{
    protected function successResponse(
        mixed $data, 
        string $message = 'تمت العملية بنجاح', 
        int $status = Response::HTTP_OK, 
        array $headers = []
    ): JsonResponse {
        if ($data instanceof JsonResource || $data instanceof AnonymousResourceCollection) {
            $response = $data->additional([
                'success' => true,
                'status_code' => $status,
                'message' => $message,
            ])->response()->setStatusCode($status);

            if (!empty($headers)) {
                foreach ($headers as $key => $value) {
                    $response->header($key, $value);
                }
            }

            return $response;
        }

        return response()->json([
            'success' => true,
            'status_code' => $status,
            'message' => $message,
            'data' => $data,
        ], $status, $headers);
    }

    protected function errorResponse(
        string $message, 
        int $status = Response::HTTP_BAD_REQUEST, 
        mixed $errors = null
    ): JsonResponse {
        $response = [
            'success' => false,
            'status_code' => $status,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }
}