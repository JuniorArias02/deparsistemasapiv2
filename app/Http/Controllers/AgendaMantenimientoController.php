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
        description: 'Retorna todas las agendas si el usuario tiene permiso "agenda_mantenimiento.listar", las programadas por el coordinador si tiene "agenda_mantenimiento.listar_coordinador", o las asignadas al técnico si tiene "agenda_mantenimiento.listar_tecnico".',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Lista de agenda de mantenimientos', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function index()
    {
        $user = \Illuminate\Support\Facades\Auth::guard('api')->user();

        if ($this->permissionService->check($user, 'agenda_mantenimiento.listar')) {
            $agendas = $this->service->getAll();
            return ApiResponse::success($agendas, 'Todas las agendas de mantenimientos');
        }

        if ($this->permissionService->check($user, 'agenda_mantenimiento.listar_coordinador')) {
            $agendas = $this->service->getByCoordinador($user->id);
            return ApiResponse::success($agendas, 'Agendas programadas por ti');
        }

        $this->permissionService->authorize('agenda_mantenimiento.listar_tecnico');
        $agendas = $this->service->getByTecnico($user->id);
    return ApiResponse::success($agendas, 'Tus agendas de mantenimientos');
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
            'titulo'        => 'required|string|max:255',
            'descripcion'   => 'nullable|string',
            'sede_id'       => 'required|exists:sedes,id',
            'fecha_inicio'  => 'required|date', 
            'fecha_fin'     => 'required|date|after:fecha_inicio',
            'asignado_a'    => 'required|exists:usuarios,id',
        ]);

        // ── 1. Validar rango horario (pasado, duración mínima/máxima) ─────────
        $errorRango = $this->service->validarRangoHorario(
            $validated['fecha_inicio'],
            $validated['fecha_fin']
        );
        if ($errorRango) {
            return ApiResponse::error($errorRango, 422);
        }

        // ── 2. Verificar que el técnico no tenga conflicto de horario ─────────
        if (!$this->service->isTecnicoDisponible(
            $validated['asignado_a'],
            $validated['fecha_inicio'],
            $validated['fecha_fin']
        )) {
            // Recuperamos el bloque en conflicto para dar un mensaje preciso
            $conflicto = \App\Models\AgendaMantenimiento::where('tecnico_id', $validated['asignado_a'])
                ->where('fecha_inicio', '<', $validated['fecha_fin'])
                ->where('fecha_fin',    '>', $validated['fecha_inicio'])
                ->with('tecnico')
                ->first();

            $nombreTecnico = $conflicto?->tecnico?->nombre_completo ?? 'El técnico seleccionado';
            $desde = $conflicto ? \Carbon\Carbon::parse($conflicto->fecha_inicio)->format('d/m/Y H:i') : '';
            $hasta = $conflicto ? \Carbon\Carbon::parse($conflicto->fecha_fin)->format('H:i') : '';

            return ApiResponse::error(
                "{$nombreTecnico} ya tiene una agenda asignada de {$desde} a {$hasta}. Por favor elige otro técnico u otro horario.",
                409
            );
        }

        try {
            $user = \Illuminate\Support\Facades\Auth::guard('api')->user();

            // ── 3. Crear el Mantenimiento ─────────────────────────────────────
            $mantenimiento = \App\Models\Mantenimiento::create([
                'titulo'         => $validated['titulo'],
                'descripcion'    => $validated['descripcion'] ?? null,
                'sede_id'        => $validated['sede_id'] ?? null,
                'creado_por'     => $validated['asignado_a'],
                'coordinador_id' => $user?->id,
                'fecha_creacion' => \Carbon\Carbon::now(),
                'esta_revisado'  => false,
            ]);

            // ── 4. Crear la Agenda ────────────────────────────────────────────
            $agenda = \App\Models\AgendaMantenimiento::create([
                'mantenimiento_id' => $mantenimiento->id,
                'titulo'           => $validated['titulo'],
                'descripcion'      => $validated['descripcion'] ?? null,
                'sede_id'          => $validated['sede_id'] ?? null,
                'fecha_inicio'     => $validated['fecha_inicio'],
                'fecha_fin'        => $validated['fecha_fin'],
                'tecnico_id'       => $validated['asignado_a'],
                'coordinador_id'   => $user?->id,
                'fecha_creacion'   => \Carbon\Carbon::now(),
            ]);

            $agenda->load(['mantenimiento', 'sede', 'tecnico', 'coordinador']);

            // ── 5. Notificación por email (no bloquea si falla) ───────────────
            try {
                $assignedUser = \App\Models\Usuario::find($validated['asignado_a']);
                if ($assignedUser && $assignedUser->correo) {
                    \Illuminate\Support\Facades\Mail::to($assignedUser->correo)
                        ->send(new \App\Mail\AgendaMantenimientoNotification(
                            $agenda, $mantenimiento, $assignedUser, $user
                        ));
                }
            } catch (\Exception $mailError) {
                \Illuminate\Support\Facades\Log::error('Error al enviar notificación de agenda: ' . $mailError->getMessage());
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
            'titulo'           => 'nullable|string|max:255',
            'descripcion'      => 'nullable|string',
            'sede_id'          => 'nullable|exists:sedes,id',
            'fecha_inicio'     => 'nullable|date',
            'fecha_fin'        => 'nullable|date|after:fecha_inicio',
            'asignado_a'       => 'nullable|exists:usuarios,id',
        ]);

        // Construir los valores finales (merge con los actuales de la agenda)
        $tecnicoId   = $validated['asignado_a']  ?? $agenda->tecnico_id;
        $fechaInicio = $validated['fecha_inicio'] ?? $agenda->fecha_inicio;
        $fechaFin    = $validated['fecha_fin']    ?? $agenda->fecha_fin;

        // ── 1. Validar rango horario ──────────────────────────────────────────
        if (isset($validated['fecha_inicio']) || isset($validated['fecha_fin'])) {
            $errorRango = $this->service->validarRangoHorario($fechaInicio, $fechaFin);
            if ($errorRango) {
                return ApiResponse::error($errorRango, 422);
            }
        }

        // ── 2. Verificar conflicto del técnico (excluyendo la agenda actual) ──
        if (!$this->service->isTecnicoDisponible($tecnicoId, $fechaInicio, $fechaFin, (int) $id)) {
            $conflicto = \App\Models\AgendaMantenimiento::where('tecnico_id', $tecnicoId)
                ->where('id', '!=', $id)
                ->where('fecha_inicio', '<', $fechaFin)
                ->where('fecha_fin',    '>', $fechaInicio)
                ->with('tecnico')
                ->first();

            $nombreTecnico = $conflicto?->tecnico?->nombre_completo ?? 'El técnico seleccionado';
            $desde = $conflicto ? \Carbon\Carbon::parse($conflicto->fecha_inicio)->format('d/m/Y H:i') : '';
            $hasta = $conflicto ? \Carbon\Carbon::parse($conflicto->fecha_fin)->format('H:i') : '';

            return ApiResponse::error(
                "{$nombreTecnico} ya tiene una agenda de {$desde} a {$hasta}. Elige otro horario o técnico.",
                409
            );
        }

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

    /**
     * GET /api/agenda-mantenimientos/disponibilidad?fecha_inicio=...&fecha_fin=...[&exclude_id=...]
     *
     * Retorna los IDs de técnicos que tienen conflicto en el rango dado.
     * El frontend lo usa para deshabilitar técnicos ya ocupados antes de guardar.
     */
    public function getDisponibilidad(Request $request)
    {
        $this->permissionService->authorize('agenda_mantenimiento.crear');

        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin'    => 'required|date|after:fecha_inicio',
            'exclude_id'   => 'nullable|integer|exists:agenda_mantenimientos,id',
        ]);

        $ocupados = $this->service->getTecnicosOcupados(
            $request->fecha_inicio,
            $request->fecha_fin,
            $request->exclude_id ? (int) $request->exclude_id : null
        );

        return ApiResponse::success(
            ['tecnicos_ocupados' => $ocupados],
            'Técnicos con conflicto de horario en el rango solicitado'
        );
    }
}
