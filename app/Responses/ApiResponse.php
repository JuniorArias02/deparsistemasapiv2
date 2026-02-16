<?php

namespace App\Responses;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Send a success response.
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    public static function success($data = [], $message = 'Operación exitosa', $statusCode = 200): JsonResponse
    {
        return response()->json([
            'mensaje' => $message,
            'objeto' => $data,
            'status' => $statusCode
        ], $statusCode);
    }

    /**
     * Send an error response.
     *
     * @param string $message
     * @param int $statusCode
     * @param mixed $data
     * @return JsonResponse
     */
    public static function error($message, $statusCode = 500, $data = []): JsonResponse
    {
        return response()->json([
            'mensaje' => $message,
            'objeto' => $data,
            'status' => $statusCode
        ], $statusCode);
    }

    /**
     * Send a created response.
     *
     * @param mixed $data
     * @param string $message
     * @return JsonResponse
     */
    public static function created($data = [], $message = 'Recurso creado exitosamente'): JsonResponse
    {
        return self::success($data, $message, 201);
    }
}
