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
}
