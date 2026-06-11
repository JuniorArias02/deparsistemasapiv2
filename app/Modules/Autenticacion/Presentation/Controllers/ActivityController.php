<?php

namespace App\Modules\Autenticacion\Presentation\Controllers;

use Illuminate\Http\Request;
use App\Responses\ApiResponse;
use App\Http\Controllers\Controller;
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
        return ApiResponse::success([], 'Heartbeat recibido');
    }
}
