<?php

namespace App\Modules\GestionCompras\Domain\Contracts;

interface CpPedidoProgramadoRepositoryInterface
{
    public function crear(array $datos): object;
    public function obtenerPorId(int $id): ?object;
    public function actualizarEstado(int $id, string $estado): bool;
    public function actualizar(int $id, array $datos): bool;
    public function eliminar(int $id): bool;
    public function obtenerProgramadosPendientes(string $fecha): array;
    public function listarConFiltros(array $filtros): array;
}
