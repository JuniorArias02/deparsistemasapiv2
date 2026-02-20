<?php

namespace App\Http\Controllers;

use App\Services\AgendaMantenimientoService;
use App\Services\PermissionService;
use App\Responses\ApiResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AgendaMantenimientoController extends Controller
{
    public function __construct(
        protected AgendaMantenimientoService $service,
        protected PermissionService $permissionService
    ) {}

    #[OA\Get(
        path: '/api/agenda-mantenimientos',
        tags: ['Agenda Mantenimientos'],
        summary: 'Listar agenda de mantenimientos',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Lista de agenda de mantenimientos', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function index()
    {
        $this->permissionService->authorize('agenda_mantenimiento.listar');

        $agendas = $this->service->getAll();
        return ApiResponse::success($agendas, 'Lista de agenda de mantenimientos');
    }

    #[OA\Post(
        path: '/api/agenda-mantenimientos',
        tags: ['Agenda Mantenimientos'],
        summary: 'Crear agenda de mantenimiento',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'mantenimiento_id', type: 'integer'),
                    new OA\Property(property: 'titulo', type: 'string'),
                    new OA\Property(property: 'descripcion', type: 'string'),
                    new OA\Property(property: 'sede_id', type: 'integer'),
                    new OA\Property(property: 'fecha_inicio', type: 'string', format: 'date-time'),
                    new OA\Property(property: 'fecha_fin', type: 'string', format: 'date-time'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Agenda creada', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function store(Request $request)
    {
        $this->permissionService->authorize('agenda_mantenimiento.crear');

        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'sede_id' => 'nullable|exists:sedes,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'asignado_a' => 'required|exists:usuarios,id',
        ]);

        try {
            $user = \Illuminate\Support\Facades\Auth::guard('api')->user();

            // 1. Create the Mantenimiento record
            $mantenimiento = \App\Models\Mantenimiento::create([
                'titulo' => $validated['titulo'],
                'descripcion' => $validated['descripcion'] ?? null,
                'sede_id' => $validated['sede_id'] ?? null,
                'creado_por' => $validated['asignado_a'],
                'nombre_receptor' => $user ? $user->id : null,
                'fecha_creacion' => \Carbon\Carbon::now(),
                'esta_revisado' => false,
            ]);

            // 2. Create the Agenda linking to the mantenimiento
            $agendaData = [
                'mantenimiento_id' => $mantenimiento->id,
                'titulo' => $validated['titulo'],
                'descripcion' => $validated['descripcion'] ?? null,
                'sede_id' => $validated['sede_id'] ?? null,
                'fecha_inicio' => $validated['fecha_inicio'],
                'fecha_fin' => $validated['fecha_fin'],
                'creado_por' => $validated['asignado_a'],
                'agendado_por' => $user ? $user->id : null,
                'fecha_creacion' => \Carbon\Carbon::now(),
            ];

            $agenda = \App\Models\AgendaMantenimiento::create($agendaData);
            $agenda->load(['mantenimiento', 'sede', 'creador', 'agendador']);

            // 3. Send email notification to the assigned person
            try {
                $assignedUser = \App\Models\Usuario::find($validated['asignado_a']);
                if ($assignedUser && $assignedUser->correo) {
                    \Illuminate\Support\Facades\Mail::to($assignedUser->correo)
                        ->send(new \App\Mail\AgendaMantenimientoNotification(
                            $agenda,
                            $mantenimiento,
                            $assignedUser,
                            $user
                        ));
                }
            } catch (\Exception $mailError) {
                \Illuminate\Support\Facades\Log::error('Error sending agenda notification email: ' . $mailError->getMessage());
            }

            return ApiResponse::success($agenda, 'Agenda de mantenimiento creada exitosamente', 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al crear agenda: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Get(
        path: '/api/agenda-mantenimientos/{id}',
        tags: ['Agenda Mantenimientos'],
        summary: 'Obtener agenda de mantenimiento',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Detalle de la agenda', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'No encontrado'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function show($id)
    {
        $this->permissionService->authorize('agenda_mantenimiento.listar');

        $agenda = $this->service->find($id);
        if (!$agenda) {
            return ApiResponse::error('Agenda de mantenimiento no encontrada', 404);
        }

        return ApiResponse::success($agenda, 'Detalle de la agenda de mantenimiento');
    }

    #[OA\Put(
        path: '/api/agenda-mantenimientos/{id}',
        tags: ['Agenda Mantenimientos'],
        summary: 'Actualizar agenda de mantenimiento',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'mantenimiento_id', type: 'integer'),
                    new OA\Property(property: 'titulo', type: 'string'),
                    new OA\Property(property: 'descripcion', type: 'string'),
                    new OA\Property(property: 'sede_id', type: 'integer'),
                    new OA\Property(property: 'fecha_inicio', type: 'string', format: 'date-time'),
                    new OA\Property(property: 'fecha_fin', type: 'string', format: 'date-time'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Actualizada exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'No encontrado'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function update(Request $request, $id)
    {
        $this->permissionService->authorize('agenda_mantenimiento.actualizar');

        $agenda = $this->service->find($id);
        if (!$agenda) {
            return ApiResponse::error('Agenda de mantenimiento no encontrada', 404);
        }

        $validated = $request->validate([
            'mantenimiento_id' => 'nullable|exists:mantenimientos,id',
            'titulo' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'sede_id' => 'nullable|exists:sedes,id',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
        ]);

        try {
            $updated = $this->service->update($id, $request->all());
            return ApiResponse::success($updated, 'Agenda de mantenimiento actualizada exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar agenda: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Delete(
        path: '/api/agenda-mantenimientos/{id}',
        tags: ['Agenda Mantenimientos'],
        summary: 'Eliminar agenda de mantenimiento',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Eliminada exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'No encontrado'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function destroy($id)
    {
        $this->permissionService->authorize('agenda_mantenimiento.eliminar');

        if ($this->service->delete($id)) {
            return ApiResponse::success(null, 'Agenda de mantenimiento eliminada exitosamente');
        }

        return ApiResponse::error('Agenda no encontrada o no se pudo eliminar', 404);
    }

    #[OA\Get(
        path: '/api/agenda-mantenimientos/mantenimiento/{mantenimiento_id}',
        tags: ['Agenda Mantenimientos'],
        summary: 'Obtener agendas por mantenimiento',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'mantenimiento_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Agendas del mantenimiento', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function getByMantenimiento($mantenimientoId)
    {
        $this->permissionService->authorize('agenda_mantenimiento.listar');

        $agendas = $this->service->getByMantenimiento($mantenimientoId);
        return ApiResponse::success($agendas, 'Agendas del mantenimiento');
    }
}
