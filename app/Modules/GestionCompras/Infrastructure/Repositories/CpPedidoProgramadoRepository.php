<?php

namespace App\Modules\GestionCompras\Infrastructure\Repositories;

use App\Models\CpPedidoProgramado;
use App\Modules\GestionCompras\Domain\Contracts\CpPedidoProgramadoRepositoryInterface;

class CpPedidoProgramadoRepository implements CpPedidoProgramadoRepositoryInterface
{
    public function crear(array $datos): object
    {
        return CpPedidoProgramado::create($datos);
    }

    public function obtenerPorId(int $id): ?object
    {
        return CpPedidoProgramado::find($id);
    }

    public function actualizarEstado(int $id, string $estado): bool
    {
        return CpPedidoProgramado::where('id', $id)->update(['estado' => $estado]) > 0;
    }

    public function actualizar(int $id, array $datos): bool
    {
        return CpPedidoProgramado::where('id', $id)->update($datos) > 0;
    }

    public function eliminar(int $id): bool
    {
        return CpPedidoProgramado::destroy($id) > 0;
    }

    public function obtenerProgramadosPendientes(string $fecha): array
    {
        return CpPedidoProgramado::where('estado', 'programado')
            ->where('fecha_programada', '<=', $fecha)
            ->get()
            ->all();
    }

    public function listarConFiltros(array $filtros): array
    {
        $query = CpPedidoProgramado::query()->with('creador');

        if (!empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }

        if (!empty($filtros['creado_por'])) {
            $query->where('creado_por', $filtros['creado_por']);
        }

        return $query->orderBy('fecha_programada', 'desc')->get()->all();
    }
}
