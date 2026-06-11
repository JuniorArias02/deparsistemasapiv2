<?php

namespace App\Modules\GestionCompras\Application\UseCases\EntregaActivosFijos;

use App\Models\CpEntregaActivosFijos;
use App\Models\CpEntregaActivosFijosItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;

class EliminarEntregaActivosFijosUseCase
{
    public function execute($id)
    {
        try {
            DB::beginTransaction();

            $entrega = CpEntregaActivosFijos::findOrFail($id);

            CpEntregaActivosFijosItem::where('entrega_activos_id', $id)->delete();

            if ($entrega->getRawOriginal('firma_quien_entrega')) {
                Storage::disk('public')->delete(str_replace(['storage/', 'public/'], '', $entrega->getRawOriginal('firma_quien_entrega')));
            }
            if ($entrega->getRawOriginal('firma_quien_recibe')) {
                Storage::disk('public')->delete(str_replace(['storage/', 'public/'], '', $entrega->getRawOriginal('firma_quien_recibe')));
            }

            $entrega->delete();

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}