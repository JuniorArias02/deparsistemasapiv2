<?php

namespace App\Modules\BuzonSugerencias\Presentation\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Responses\ApiResponse;
use App\Modules\BuzonSugerencias\Application\UseCases\RegistrarSugerenciaUseCase;
use App\Modules\BuzonSugerencias\Application\UseCases\CargarEvidenciaVisualUseCase;
use App\Modules\BuzonSugerencias\Application\UseCases\SeguimientoTicketUseCase;
use App\Modules\BuzonSugerencias\Application\UseCases\ResponderSugerenciaUseCase;
use App\Modules\BuzonSugerencias\Application\UseCases\CambiarEstadoUseCase;
use App\Modules\BuzonSugerencias\Application\UseCases\ListarSugerenciasUseCase;
use App\Modules\BuzonSugerencias\Application\UseCases\AsignarResponsableUseCase;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class BuzonSugerenciaController extends Controller
{
    #[OA\Post(
        path: '/api/buzon-sugerencias',
        summary: 'Registrar una nueva sugerencia',
        tags: ['Buzón de Sugerencias'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['asunto', 'observaciones'],
                properties: [
                    new OA\Property(property: 'asunto', type: 'string', example: 'Problema con el aire acondicionado'),
                    new OA\Property(property: 'observaciones', type: 'string', example: 'El aire de la sala de reuniones no funciona.'),
                    new OA\Property(property: 'prioridad', type: 'string', example: 'Media')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Sugerencia registrada exitosamente')
        ]
    )]
    public function store(Request $request, RegistrarSugerenciaUseCase $useCase, CargarEvidenciaVisualUseCase $archivosUseCase)
    {
        $request->validate([
            'asunto' => 'required|string|max:255',
            'observaciones' => 'required|string',
            'prioridad' => 'nullable|string|in:Baja,Media,Alta',
            'archivos' => 'nullable|array',
            'archivos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->except('archivos');
        $data['creado_por'] = Auth::id();

        $sugerencia = $useCase->execute($data);

        if ($request->hasFile('archivos')) {
            $archivosUseCase->execute($sugerencia->id, $request->file('archivos'));
        }

        return ApiResponse::created($sugerencia, 'Sugerencia registrada exitosamente.');
    }

    #[OA\Post(
        path: '/api/buzon-sugerencias/{id}/adjuntos',
        summary: 'Subir evidencias visuales a una sugerencia',
        tags: ['Buzón de Sugerencias'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'archivos[]', type: 'array', items: new OA\Items(type: 'string', format: 'binary'))
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Adjuntos subidos exitosamente')
        ]
    )]
    public function uploadAdjuntos(Request $request, $id, CargarEvidenciaVisualUseCase $useCase)
    {
        $request->validate([
            'archivos' => 'required|array',
            'archivos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $adjuntos = $useCase->execute($id, $request->file('archivos'));

        return ApiResponse::created($adjuntos, 'Evidencias subidas exitosamente.');
    }

    #[OA\Get(
        path: '/api/buzon-sugerencias/{codigo}',
        summary: 'Consultar seguimiento de un ticket',
        tags: ['Buzón de Sugerencias'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'codigo', in: 'path', required: true, description: 'Código de ticket (Ej: SUG-2026-001)', schema: new OA\Schema(type: 'string'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Detalles de la sugerencia')
        ]
    )]
    public function show($codigo, SeguimientoTicketUseCase $useCase)
    {
        try {
            $sugerencia = $useCase->execute($codigo);
            return ApiResponse::success($sugerencia, 'Ticket obtenido exitosamente.');
        } catch (\Exception $e) {
            return ApiResponse::error('Ticket no encontrado.', 404);
        }
    }

    #[OA\Get(
        path: '/api/buzon-sugerencias',
        summary: 'Listar sugerencias',
        tags: ['Buzón de Sugerencias'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'pendientes', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'mias', in: 'query', schema: new OA\Schema(type: 'boolean'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Listado de sugerencias')
        ]
    )]
    public function index(Request $request, ListarSugerenciasUseCase $useCase)
    {
        $filters = [];
        if ($request->boolean('mias')) {
            $filters['creado_por'] = Auth::id();
        }
        if ($request->boolean('pendientes')) {
            $filters['pendientes'] = true;
        }

        $sugerencias = $useCase->execute($filters);

        return ApiResponse::success($sugerencias, 'Listado obtenido exitosamente.');
    }

    #[OA\Post(
        path: '/api/buzon-sugerencias/{id}/comentarios',
        summary: 'Agregar comentario a una sugerencia',
        tags: ['Buzón de Sugerencias'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['mensaje'],
                properties: [
                    new OA\Property(property: 'mensaje', type: 'string')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Comentario agregado')
        ]
    )]
    public function storeComentario(Request $request, $id, ResponderSugerenciaUseCase $useCase)
    {
        $request->validate([
            'mensaje' => 'required|string',
        ]);

        $comentario = $useCase->execute($id, Auth::id(), $request->mensaje);

        return ApiResponse::created($comentario, 'Comentario agregado exitosamente.');
    }

    #[OA\Patch(
        path: '/api/buzon-sugerencias/{id}/estado',
        summary: 'Cambiar estado de una sugerencia',
        tags: ['Buzón de Sugerencias'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['estado_id'],
                properties: [
                    new OA\Property(property: 'estado_id', type: 'integer')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Estado actualizado')
        ]
    )]
    public function updateEstado(Request $request, $id, CambiarEstadoUseCase $useCase)
    {
        $request->validate([
            'estado_id' => 'required|exists:estados_ticket,id',
        ]);

        $sugerencia = $useCase->execute($id, $request->estado_id);

        return ApiResponse::success($sugerencia, 'Estado actualizado exitosamente.');
    }

    #[OA\Patch(
        path: '/api/buzon-sugerencias/{id}/asignar',
        summary: 'Asignar sugerencia a un responsable',
        tags: ['Buzón de Sugerencias'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['usuario_id'],
                properties: [
                    new OA\Property(property: 'usuario_id', type: 'integer')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Responsable asignado')
        ]
    )]
    public function asignarResponsable(Request $request, $id, \App\Modules\BuzonSugerencias\Application\UseCases\AsignarResponsableUseCase $useCase)
    {
        $request->validate([
            'usuario_id' => 'required|exists:usuarios,id',
        ]);

        $sugerencia = $useCase->execute($id, $request->usuario_id);

        return ApiResponse::success($sugerencia, 'Responsable asignado exitosamente.');
    }

    #[OA\Get(
        path: '/api/buzon-sugerencias/no-leidos-count',
        summary: 'Obtener cantidad de mensajes no leídos',
        tags: ['Buzón de Sugerencias'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Cantidad de mensajes no leídos')
        ]
    )]
    public function getNoLeidosCount(Request $request, \App\Modules\BuzonSugerencias\Application\UseCases\ContarMensajesNoLeidosUseCase $useCase)
    {
        $isAgente = false;
        
        if (method_exists($request->user(), 'hasPermissionTo')) {
            $isAgente = $request->user()->hasPermissionTo('buzon.agente');
        } else {
            $isAgente = \Illuminate\Support\Facades\DB::table('rol_permisos')
                ->join('permisos', 'rol_permisos.permiso_id', '=', 'permisos.id')
                ->where('rol_permisos.rol_id', $request->user()->rol_id)
                ->where('permisos.nombre', 'buzon.agente')
                ->exists();
        }

        $count = $useCase->execute(\Illuminate\Support\Facades\Auth::id(), $isAgente);

        return ApiResponse::success(['count' => $count], 'Conteo obtenido exitosamente.');
    }

    #[OA\Get(
        path: '/api/buzon-sugerencias/tickets-no-leidos',
        summary: 'Obtener tickets con mensajes no leídos',
        tags: ['Buzón de Sugerencias'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Listado de tickets con mensajes no leídos')
        ]
    )]
    public function getTicketsNoLeidos(Request $request, \App\Modules\BuzonSugerencias\Application\UseCases\ObtenerTicketsNoLeidosUseCase $useCase)
    {
        $isAgente = false;
        
        if (method_exists($request->user(), 'hasPermissionTo')) {
            $isAgente = $request->user()->hasPermissionTo('buzon.agente');
        } else {
            $isAgente = \Illuminate\Support\Facades\DB::table('rol_permisos')
                ->join('permisos', 'rol_permisos.permiso_id', '=', 'permisos.id')
                ->where('rol_permisos.rol_id', $request->user()->rol_id)
                ->where('permisos.nombre', 'buzon.agente')
                ->exists();
        }

        $tickets = $useCase->execute(\Illuminate\Support\Facades\Auth::id(), $isAgente);

        return ApiResponse::success($tickets, 'Tickets con mensajes no leídos.');
    }

    #[OA\Post(
        path: '/api/buzon-sugerencias/{id}/leer-comentarios',
        summary: 'Marcar comentarios de un ticket como leídos',
        tags: ['Buzón de Sugerencias'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Comentarios marcados como leídos')
        ]
    )]
    public function marcarComentariosLeidos($id, \App\Modules\BuzonSugerencias\Application\UseCases\MarcarComentariosLeidosUseCase $useCase)
    {
        $useCase->execute($id, Auth::id());
        return ApiResponse::success(null, 'Comentarios marcados como leídos.');
    }
}
