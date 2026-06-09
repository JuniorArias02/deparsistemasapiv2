<?php

namespace App\Modules\GestionSistemas\Application\DTOs;

class PerifericoDTO
{
    public int $inventarioId;
    public int $cantidad;
    public ?string $observaciones;

    public function __construct(int $inventarioId, int $cantidad, ?string $observaciones = null)
    {
        $this->inventarioId = $inventarioId;
        $this->cantidad = $cantidad;
        $this->observaciones = $observaciones;
    }
}
