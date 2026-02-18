<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Responses\ApiResponse;
use OpenApi\Attributes as OA;

class ActivityController extends Controller
{
    /**
     * Update user activity heartbeat.
     */
    #[OA\Post(
        path: '/api/heartbeat',
        tags: ['Actividad'],
        summary: 'Registrar actividad (Heartbeat)',
        description: 'Actualiza la fecha de última actividad del usuario. Debe llamarse periódicamente desde el frontend.',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Actividad registrada',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')
            )
        ]
    )]
    public function heartbeat(Request $request)
    {
        // The UpdateLastActivity middleware handles the actual update.
        // This endpoint just ensures the middleware is triggered.
        return ApiResponse::success([], 'Heartbeat recibido');
    }
}
