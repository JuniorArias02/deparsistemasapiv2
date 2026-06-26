<?php

namespace App\Modules\Dashboard\Application\UseCases;

use App\Models\Usuario;
use App\Models\Sede;

class ObtenerEstadisticasAdminUseCase
{
    public function __construct(
        protected ObtenerEstadisticasSistemasUseCase $sistemasUseCase,
        protected ObtenerEstadisticasComprasUseCase $comprasUseCase
    ) {}

    public function execute(): array
    {
        $sistemas = $this->sistemasUseCase->execute();
        $compras = $this->comprasUseCase->execute();

        return array_merge($sistemas, $compras, [
            'total_usuarios' => Usuario::count(),
            'total_sedes' => Sede::count(),
        ]);
    }
}
