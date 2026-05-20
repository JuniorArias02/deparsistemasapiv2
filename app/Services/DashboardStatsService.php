<?php

namespace App\Services;

use App\Models\PcEquipo;
use App\Models\PcEntrega;
use App\Models\PcMantenimiento;
use App\Models\PcDevuelto;
use App\Models\CpPedido;
use App\Models\CpProducto;
use App\Models\CpProveedor;
use App\Models\Usuario;
use App\Models\Sede;
use App\Models\CpTipoSolicitud;
use Illuminate\Support\Facades\DB;

class DashboardStatsService
{
    public function getSistemasStats(): array
    {
        return [
            'total_equipos' => PcEquipo::count(),
            'equipos_disponibles' => PcEquipo::where('estado', 'disponible')->count(),
            'equipos_asignados' => PcEquipo::where('estado', 'asignado')->count(),
            'equipos_mantenimiento' => PcEquipo::where('estado', 'mantenimiento')->count(),
            'total_entregas' => PcEntrega::count(),
            'total_devoluciones' => PcDevuelto::count(),
            'total_mantenimientos' => PcMantenimiento::count(),
            'ultimas_entregas' => PcEntrega::with(['equipo', 'funcionario'])->latest('fecha_entrega')->take(5)->get(),
        ];
    }

    public function getComprasStats(): array
    {
        return [
            'total_pedidos' => CpPedido::count(),
            'pedidos_pendientes' => CpPedido::where('estado_compras', 'pendiente')->count(),
            'pedidos_aprobados' => CpPedido::where('estado_compras', 'aprobado')->count(),
            'total_productos' => CpProducto::count(),
            'total_proveedores' => CpProveedor::count(),
            // Remove 'proveedor' relation as it does not exist
            'ultimos_pedidos' => CpPedido::with(['elaboradoPor', 'sede'])->latest('fecha_solicitud')->take(5)->get(),
            'estadisticas_tiempo' => $this->getPedidosTimeStats(),
            'desglose_solicitudes' => $this->getDesgloseSolicitudes(),
        ];
    }

    public function getAdminStats(): array
    {
        // Combine stats
        $sistemas = $this->getSistemasStats();
        $compras = $this->getComprasStats();

        return array_merge($sistemas, $compras, [
            'total_usuarios' => Usuario::count(),
            'total_sedes' => Sede::count(),
        ]);
    }

    public function getPedidosTimeStats(): array
    {
        // 1. Average Compras processing time (solicitud -> compra)
        $avgSecondsCompras = CpPedido::whereNotNull('fecha_solicitud')
            ->whereNotNull('fecha_compra')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, fecha_solicitud, fecha_compra)) as avg_seconds')
            ->value('avg_seconds');

        // 2. Average Gerencia processing time (compra -> gerencia)
        $avgSecondsGerencia = CpPedido::whereNotNull('fecha_compra')
            ->whereNotNull('fecha_gerencia')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, fecha_compra, fecha_gerencia)) as avg_seconds')
            ->value('avg_seconds');

        // 3. Average Total time (solicitud -> gerencia)
        $avgSecondsTotal = CpPedido::whereNotNull('fecha_solicitud')
            ->whereNotNull('fecha_gerencia')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, fecha_solicitud, fecha_gerencia)) as avg_seconds')
            ->value('avg_seconds');

        // 4. Top 5 fastest processed orders (fully approved)
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

        // 5. Top 5 slowest processed orders (fully approved)
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

        // 6. Distribution of processing times (e.g. <= 24h, 1-3 days, > 3 days)
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

    public function getDesgloseSolicitudes(): array
    {
        $total = CpPedido::count();

        // Get count grouped by tipo_solicitud
        $counts = CpPedido::select('tipo_solicitud', DB::raw('count(*) as total_pedidos'))
            ->groupBy('tipo_solicitud')
            ->get();

        // Load the types
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
}
