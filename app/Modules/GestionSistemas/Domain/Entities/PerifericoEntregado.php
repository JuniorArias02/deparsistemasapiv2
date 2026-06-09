<?php

namespace App\Modules\GestionSistemas\Domain\Entities;

class PerifericoEntregado
{
    private ?int $id;
    private int $inventarioId;
    private int $cantidad;
    private ?string $observaciones;

    public function __construct(
        int $inventarioId,
        int $cantidad,
        ?string $observaciones = null,
        ?int $id = null
    ) {
        $this->inventarioId = $inventarioId;
        $this->cantidad = $cantidad;
        $this->observaciones = $observaciones;
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInventarioId(): int
    {
        return $this->inventarioId;
    }

    public function getCantidad(): int
    {
        return $this->cantidad;
    }

    public function getObservaciones(): ?string
    {
        return $this->observaciones;
    }
}
