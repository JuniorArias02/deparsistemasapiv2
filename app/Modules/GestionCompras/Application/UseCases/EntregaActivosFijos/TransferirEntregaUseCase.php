<?php

namespace App\Modules\GestionCompras\Application\UseCases\EntregaActivosFijos;

use App\Models\CpEntregaActivosFijos;
use App\Models\CpEntregaActivosFijosItem;
use Illuminate\Support\Facades\DB;
use Exception;

class TransferirEntregaUseCase
{
    public function execute($id, $nuevoCoordinadorId, $nuevoPersonalId = null)
    {
        DB::beginTransaction();

        try {
            $original = CpEntregaActivosFijos::with('items')->findOrFail($id);

            $nuevaActa = new CpEntregaActivosFijos();
            $nuevaActa->personal_id = $nuevoPersonalId ?? $original->personal_id;
            $nuevaActa->sede_id = $original->sede_id;
            $nuevaActa->proceso_solicitante = $original->proceso_solicitante;
            $nuevaActa->coordinador_id = $nuevoCoordinadorId;
            $nuevaActa->fecha_entrega = now();
            $nuevaActa->firma_quien_entrega = null;
            $nuevaActa->firma_quien_recibe = null;
            $nuevaActa->save();

            foreach ($original->items as $item) {
                $nuevoItem = new CpEntregaActivosFijosItem();
                $nuevoItem->item_id = $item->item_id;
                $nuevoItem->es_accesorio = $item->es_accesorio;
                $nuevoItem->accesorio_descripcion = $item->accesorio_descripcion;
                $nuevoItem->entrega_activos_id = $nuevaActa->id;
                $nuevoItem->save();
            }

            DB::commit();
            return $nuevaActa;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}