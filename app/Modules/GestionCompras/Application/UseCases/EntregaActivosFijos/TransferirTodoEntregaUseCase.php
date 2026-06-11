<?php

namespace App\Modules\GestionCompras\Application\UseCases\EntregaActivosFijos;

use App\Models\CpEntregaActivosFijos;

class TransferirTodoEntregaUseCase
{
    public function __construct(protected TransferirEntregaUseCase $transferirEntregaUseCase) {}

    public function execute($coordinadorViejoId, $coordinadorNuevoId)
    {
        $actas = CpEntregaActivosFijos::where('coordinador_id', $coordinadorViejoId)->get();
        
        $transferredCount = 0;
        foreach ($actas as $acta) {
            $this->transferirEntregaUseCase->execute($acta->id, $coordinadorNuevoId);
            $transferredCount++;
        }

        return $transferredCount;
    }
}