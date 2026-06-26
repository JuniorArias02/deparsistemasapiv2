<?php

namespace App\Modules\Dashboard\Application\UseCases;

use App\Models\CpPedido;
use App\Models\CpProducto;
use App\Models\CpProveedor;
use App\Models\CpTipoSolicitud;
use Illuminate\Support\Facades\DB;

class ObtenerEstadisticasComprasUseCase
{
    public function execute(): array
    {
        return [
            'total_pedidos' => CpPedido::count(),
            'pedidos_pendientes' => CpPedido::where('estado_compras', 'pendiente')->count(),
            'pedidos_aprobados' => CpPedido::where('estado_compras', 'aprobado')->count(),
            'total_productos' => CpProducto::count(),
            'total_proveedores' => CpProveedor::count(),
            'ultimos_pedidos' => CpPedido::with(['elaboradoPor', 'sede'])->latest('fecha_solicitud')->take(5)->get(),
            'estadisticas_tiempo' => $this->getPedidosTimeStats(),
            'desglose_solicitudes' => $this->getDesgloseSolicitudes(),
            'desglose_por_sede' => $this->getDesglosePorSedes(),
            'desglose_por_proceso' => $this->getDesglosePorProcesos(),
        ];
    }

    private function getPedidosTimeStats(): array
    {
        $avgSecondsCompras = CpPedido::whereNotNull('fecha_solicitud')
            ->whereNotNull('fecha_compra')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, fecha_solicitud, fecha_compra)) as avg_seconds')
            ->value('avg_seconds');

        $avgSecondsGerencia = CpPedido::whereNotNull('fecha_compra')
            ->whereNotNull('fecha_gerencia')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, fecha_compra, fecha_gerencia)) as avg_seconds')
            ->value('avg_seconds');

        $avgSecondsTotal = CpPedido::whereNotNull('fecha_solicitud')
            ->whereNotNull('fecha_gerencia')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, fecha_solicitud, fecha_gerencia)) as avg_seconds')
            ->value('avg_seconds');

        $fastestPedidos = CpPedido::with(['elaboradoPor', 'sede'])
            ->whereNotNull('fecha_solicitud')
            ->whereNotNull('fecha_gerencia')
            ->select('*')
            ->selectRaw('TIMESTAMPDIFF(SECOND, fecha_solicitud, fecha_gerencia) as duration_seconds')
            ->orderBy('duration_seconds', 'asc')
            ->take(5)
            ->get()
            ->map(function ($pedido) {
                return [
                    'id' => $pedido->id,
                    'consecutivo' => $pedido->consecutivo,
                    'elaborado_por' => $pedido->elaboradoPor?->nombre ?? 'N/A',
                    'sede' => $pedido->sede?->nombre ?? 'N/A',
                    'duracion' => $this->formatSeconds($pedido->duration_seconds),
                    'duracion_segundos' => $pedido->duration_seconds,
                    'fecha_solicitud' => $pedido->fecha_solicitud,
                    'fecha_gerencia' => $pedido->fecha_gerencia,
                ];
            });

        $slowestPedidos = CpPedido::with(['elaboradoPor', 'sede'])
            ->whereNotNull('fecha_solicitud')
            ->whereNotNull('fecha_gerencia')
            ->select('*')
            ->selectRaw('TIMESTAMPDIFF(SECOND, fecha_solicitud, fecha_gerencia) as duration_seconds')
            ->orderBy('duration_seconds', 'desc')
            ->take(5)
            ->get()
            ->map(function ($pedido) {
                return [
                    'id' => $pedido->id,
                    'consecutivo' => $pedido->consecutivo,
                    'elaborado_por' => $pedido->elaboradoPor?->nombre ?? 'N/A',
                    'sede' => $pedido->sede?->nombre ?? 'N/A',
                    'duracion' => $this->formatSeconds($pedido->duration_seconds),
                    'duracion_segundos' => $pedido->duration_seconds,
                    'fecha_solicitud' => $pedido->fecha_solicitud,
                    'fecha_gerencia' => $pedido->fecha_gerencia,
                ];
            });

        $totalApproved = CpPedido::whereNotNull('fecha_solicitud')->whereNotNull('fecha_gerencia')->count();
        $under24h = CpPedido::whereNotNull('fecha_solicitud')
            ->whereNotNull('fecha_gerencia')
            ->whereRaw('TIMESTAMPDIFF(SECOND, fecha_solicitud, fecha_gerencia) <= 86400')
            ->count();
        $under3Days = CpPedido::whereNotNull('fecha_solicitud')
            ->whereNotNull('fecha_gerencia')
            ->whereRaw('TIMESTAMPDIFF(SECOND, fecha_solicitud, fecha_gerencia) > 86400')
            ->whereRaw('TIMESTAMPDIFF(SECOND, fecha_solicitud, fecha_gerencia) <= 259200')
            ->count();
        $over3Days = CpPedido::whereNotNull('fecha_solicitud')
            ->whereNotNull('fecha_gerencia')
            ->whereRaw('TIMESTAMPDIFF(SECOND, fecha_solicitud, fecha_gerencia) > 259200')
            ->count();

        return [
            'tiempo_promedio_compras' => $this->formatSeconds($avgSecondsCompras),
            'tiempo_promedio_compras_segundos' => (float) $avgSecondsCompras,
            'tiempo_promedio_gerencia' => $this->formatSeconds($avgSecondsGerencia),
            'tiempo_promedio_gerencia_segundos' => (float) $avgSecondsGerencia,
            'tiempo_promedio_total' => $this->formatSeconds($avgSecondsTotal),
            'tiempo_promedio_total_segundos' => (float) $avgSecondsTotal,
            'pedidos_mas_rapidos' => $fastestPedidos,
            'pedidos_mas_lentos' => $slowestPedidos,
            'distribucion_tiempos' => [
                'total_aprobados' => $totalApproved,
                'menos_24h' => $under24h,
                'entre_1_y_3_dias' => $under3Days,
                'mas_3_dias' => $over3Days,
                'porcentaje_menos_24h' => $totalApproved > 0 ? round(($under24h / $totalApproved) * 100, 1) : 0,
                'porcentaje_1_3_dias' => $totalApproved > 0 ? round(($under3Days / $totalApproved) * 100, 1) : 0,
                'porcentaje_mas_3_dias' => $totalApproved > 0 ? round(($over3Days / $totalApproved) * 100, 1) : 0,
            ]
        ];
    }

    private function formatSeconds($seconds)
    {
        if ($seconds === null || $seconds < 0) return 'N/A';
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        if ($days > 0) {
            return "{$days}d {$hours}h {$minutes}m";
        }
        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }
        return "{$minutes}m";
    }

    private function getDesgloseSolicitudes(): array
    {
        $total = CpPedido::count();

        $counts = CpPedido::select('tipo_solicitud', DB::raw('count(*) as total_pedidos'))
            ->groupBy('tipo_solicitud')
            ->get();

        $tipos = CpTipoSolicitud::all()->keyBy('id');

        $resultado = [];
        foreach ($counts as $item) {
            $tipoId = $item->tipo_solicitud;
            $tipoNombre = isset($tipos[$tipoId]) ? $tipos[$tipoId]->nombre : 'No especificado';
            $count = $item->total_pedidos;

            $resultado[] = [
                'id' => $tipoId,
                'nombre' => $tipoNombre,
                'cantidad' => $count,
                'porcentaje' => $total > 0 ? round(($count / $total) * 100, 1) : 0,
            ];
        }

        return [
            'total_pedidos' => $total,
            'tipos' => $resultado,
        ];
    }

    private function getDesglosePorSedes(): array
    {
        return CpPedido::select('sede_id', DB::raw('count(*) as cantidad'))
            ->with('sede:id,nombre')
            ->groupBy('sede_id')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->sede_id,
                    'nombre' => $item->sede ? $item->sede->nombre : 'Sin Sede',
                    'cantidad' => $item->cantidad,
                ];
            })
            ->sortByDesc('cantidad')
            ->values()
            ->toArray();
    }

    private function getDesglosePorProcesos(): array
    {
        return CpPedido::select('proceso_solicitante', DB::raw('count(*) as cantidad'))
            ->with(['solicitante', 'solicitante.sede'])
            ->groupBy('proceso_solicitante')
            ->get()
            ->map(function ($item) {
                $nombreProceso = $item->solicitante ? $item->solicitante->nombre : 'Sin Proceso';
                $nombreSede = ($item->solicitante && $item->solicitante->sede) ? $item->solicitante->sede->nombre : '';
                
                $nombreCompleto = $nombreSede ? "{$nombreProceso} ({$nombreSede})" : $nombreProceso;

                return [
                    'id' => $item->proceso_solicitante,
                    'nombre' => $nombreCompleto,
                    'proceso_nombre' => $nombreProceso,
                    'sede_nombre' => $nombreSede,
                    'cantidad' => $item->cantidad,
                ];
            })
            ->sortByDesc('cantidad')
            ->values()
            ->toArray();
    }
}
