<?php
namespace App\Modules\GestionInfraestructura\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\GestionInfraestructura\Application\UseCases\AgendaMantenimiento\ListarAgendaUseCase;
use App\Modules\GestionInfraestructura\Application\UseCases\AgendaMantenimiento\CrearAgendaUseCase;
use App\Modules\GestionInfraestructura\Application\UseCases\AgendaMantenimiento\ObtenerAgendaUseCase;
use App\Modules\GestionInfraestructura\Application\UseCases\AgendaMantenimiento\ActualizarAgendaUseCase;
use App\Modules\GestionInfraestructura\Application\UseCases\AgendaMantenimiento\EliminarAgendaUseCase;
use App\Modules\GestionInfraestructura\Application\UseCases\AgendaMantenimiento\ObtenerAgendaPorFiltroUseCase;
use App\Modules\GestionInfraestructura\Application\UseCases\AgendaMantenimiento\ValidarDisponibilidadUseCase;
use App\Services\PermissionService;
use App\Responses\ApiResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AgendaMantenimientoController extends Controller
{
    public function __construct(
        protected PermissionService $permissionService,
        protected ListarAgendaUseCase $listarUseCase,
        protected CrearAgendaUseCase $crearUseCase,
        protected ObtenerAgendaUseCase $obtenerUseCase,
        protected ActualizarAgendaUseCase $actualizarUseCase,
        protected EliminarAgendaUseCase $eliminarUseCase,
        protected ObtenerAgendaPorFiltroUseCase $porFiltroUseCase,
        protected ValidarDisponibilidadUseCase $validadorUseCase
    ) {}

    public function index()
    {
        $this->permissionService->authorize('agenda_mantenimiento.listar');
        return ApiResponse::success($this->listarUseCase->execute(), 'Agenda de mantenimiento');
    }

    public function store(Request $request)
    {
        $this->permissionService->authorize('agenda_mantenimiento.crear');
        $request->validate([
            'titulo' => 'required|string',
            'descripcion' => 'nullable|string',
            'mantenimiento_id' => 'nullable|exists:mantenimientos,id',
            'sede_id' => 'required|exists:sedes,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'tecnico_id' => 'nullable|exists:usuarios,id',
            'tecnicos' => 'nullable|array',
            'tecnicos.*' => 'exists:usuarios,id',
        ]);
        
        try {
            $data = $request->all();
            $creados = [];
            
            // Si el frontend envía un array de técnicos, creamos un registro por cada uno
            if (!empty($data['tecnicos'])) {
                foreach ($data['tecnicos'] as $tId) {
                    $itemData = $data;
                    $itemData['tecnico_id'] = $tId;
                    unset($itemData['tecnicos']);
                    $creados[] = $this->crearUseCase->execute($itemData);
                }
            } else {
                $creados[] = $this->crearUseCase->execute($data);
            }
            
            return ApiResponse::success($creados, 'Agendamiento creado exitosamente', 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al crear agendamiento: ' . $e->getMessage(), 400);
        }
    }

    public function show($id)
    {
        $this->permissionService->authorize('agenda_mantenimiento.listar');
        $item = $this->obtenerUseCase->execute($id);
        if (!$item) return ApiResponse::error('Agendamiento no encontrado', 404);
        return ApiResponse::success($item, 'Detalle de agendamiento');
    }

    public function update(Request $request, $id)
    {
        $this->permissionService->authorize('agenda_mantenimiento.actualizar');
        $request->validate([
            'titulo' => 'nullable|string',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after:fecha_inicio',
            'tecnico_id' => 'nullable|exists:usuarios,id',
            'mantenimiento_id' => 'nullable|exists:mantenimientos,id',
            'sede_id' => 'nullable|exists:sedes,id',
        ]);

        try {
            $item = $this->actualizarUseCase->execute($id, $request->all());
            if (!$item) return ApiResponse::error('Agendamiento no encontrado', 404);
            return ApiResponse::success($item, 'Agendamiento actualizado exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar agendamiento: ' . $e->getMessage(), 400);
        }
    }

    public function destroy($id)
    {
        $this->permissionService->authorize('agenda_mantenimiento.eliminar');
        if ($this->eliminarUseCase->execute($id)) {
            return ApiResponse::success(null, 'Agendamiento eliminado exitosamente');
        }
        return ApiResponse::error('Agendamiento no encontrado', 404);
    }

    public function getByMantenimiento($mantenimientoId)
    {
        $this->permissionService->authorize('agenda_mantenimiento.listar');
        return ApiResponse::success($this->porFiltroUseCase->execute('mantenimiento_id', $mantenimientoId), 'Agenda del mantenimiento');
    }

    public function getByTecnico($userId)
    {
        $this->permissionService->authorize('agenda_mantenimiento.listar');
        return ApiResponse::success($this->porFiltroUseCase->execute('tecnico_id', $userId), 'Agenda del técnico');
    }

    public function getByCoordinador($userId)
    {
        $this->permissionService->authorize('agenda_mantenimiento.listar');
        return ApiResponse::success($this->porFiltroUseCase->execute('coordinador_id', $userId), 'Agenda del coordinador');
    }

    public function getDisponibilidad(Request $request)
    {
        $this->permissionService->authorize('agenda_mantenimiento.listar');
        $request->validate([
            'tecnico_id' => 'required|exists:usuarios,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'exclude_id' => 'nullable|exists:agenda_mantenimientos,id',
        ]);

        $disponible = $this->validadorUseCase->isTecnicoDisponible(
            $request->tecnico_id,
            $request->fecha_inicio,
            $request->fecha_fin,
            $request->exclude_id
        );

        return response()->json([
            'disponible' => $disponible,
            'mensaje' => $disponible ? 'El técnico está disponible.' : 'El técnico ya tiene otra actividad asignada en ese horario.'
        ]);
    }

    public function ocupados(Request $request)
    {
        $this->permissionService->authorize('agenda_mantenimiento.listar');
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'exclude_id' => 'nullable|exists:agenda_mantenimientos,id',
        ]);

        $ocupados = $this->validadorUseCase->getTecnicosOcupados(
            $request->fecha_inicio,
            $request->fecha_fin,
            $request->exclude_id
        );

        return response()->json([
            'ocupados' => $ocupados,
        ]);
    }
}