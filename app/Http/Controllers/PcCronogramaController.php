<?php

namespace App\Http\Controllers;

use App\Services\PcEquipoService;
use App\Responses\ApiResponse;
use Illuminate\Http\Request;
use App\Models\PcEquipo;

class PcCronogramaController extends Controller
{
    public function __construct(
        protected PcEquipoService $equipoService
    ) {}

    /**
     * Obtiene el listado de equipos con su información de mantenimiento calculada.
     */
    public function index()
    {
        $equipos = PcEquipo::with(['mantenimientos' => function($q) {
            $q->orderBy('fecha', 'desc');
        }, 'sede', 'area', 'responsable'])->get();

        $cronograma = $equipos->map(function($equipo) {
            $mantoInfo = $this->equipoService->calculateMaintenanceInfo($equipo);
            
            // Determinar estado visual
            $estadoManto = 'al_dia';
            if ($mantoInfo['dias_restantes'] === null) {
                $estadoManto = 'sin_registro';
            } elseif ($mantoInfo['dias_restantes'] <= 0) {
                $estadoManto = 'vencido';
            } elseif ($mantoInfo['dias_restantes'] <= 30) {
                $estadoManto = 'proximo';
            }

            return [
                'id' => $equipo->id,
                'nombre_equipo' => $equipo->nombre_equipo,
                'numero_inventario' => $equipo->numero_inventario,
                'serial' => $equipo->serial,
                'marca' => $equipo->marca,
                'modelo' => $equipo->modelo,
                'tipo' => $equipo->tipo,
                'sede' => $equipo->sede?->nombre,
                'area' => $equipo->area?->nombre,
                'responsable' => $equipo->responsable?->nombre_completo,
                'estado_equipo' => $equipo->estado,
                'mantenimiento' => array_merge($mantoInfo, ['estado_manto' => $estadoManto])
            ];
        });

        return ApiResponse::success($cronograma, 'Cronograma de mantenimientos obtenido exitosamente');
    }
}
