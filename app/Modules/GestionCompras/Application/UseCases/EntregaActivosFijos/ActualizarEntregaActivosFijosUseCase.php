<?php

namespace App\Modules\GestionCompras\Application\UseCases\EntregaActivosFijos;

use App\Models\CpEntregaActivosFijos;
use App\Models\CpEntregaActivosFijosItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;

class ActualizarEntregaActivosFijosUseCase
{
    use HandleSignatureTrait;

    public function execute($id, array $data, $firmaEntregaFile = null, $firmaRecibeFile = null, $useStoredSignatureEntrega = false, $useStoredSignatureRecibe = false, $user = null)
    {
        try {
            DB::beginTransaction();

            $entrega = CpEntregaActivosFijos::findOrFail($id);
            $updateData = $data;

            if ($firmaEntregaFile || $useStoredSignatureEntrega) {
                $path = $this->handleSignature($firmaEntregaFile, $useStoredSignatureEntrega, $user, 'entrega_firma_entrega_edit');
                if ($path) {
                    if ($entrega->getRawOriginal('firma_quien_entrega')) {
                        Storage::disk('public')->delete(str_replace(['storage/', 'public/'], '', $entrega->getRawOriginal('firma_quien_entrega')));
                    }
                    $updateData['firma_quien_entrega'] = 'storage/' . $path;
                }
            }

            if ($firmaRecibeFile || $useStoredSignatureRecibe) {
                $path = $this->handleSignature($firmaRecibeFile, $useStoredSignatureRecibe, $user, 'entrega_firma_recibe_edit');
                if ($path) {
                    if ($entrega->getRawOriginal('firma_quien_recibe')) {
                        Storage::disk('public')->delete(str_replace(['storage/', 'public/'], '', $entrega->getRawOriginal('firma_quien_recibe')));
                    }
                    $updateData['firma_quien_recibe'] = 'storage/' . $path;
                }
            }

            $itemsData = null;
            if (isset($updateData['items'])) {
                $itemsData = $updateData['items'];
                unset($updateData['items']);
            }

            $entrega->update($updateData);

            if ($itemsData !== null && is_array($itemsData)) {
                CpEntregaActivosFijosItem::where('entrega_activos_id', $entrega->id)->delete();
                foreach ($itemsData as $item) {
                    CpEntregaActivosFijosItem::create([
                        'item_id' => $item['item_id'],
                        'es_accesorio' => $item['es_accesorio'] ?? false,
                        'accesorio_descripcion' => $item['accesorio_descripcion'] ?? null,
                        'entrega_activos_id' => $entrega->id,
                    ]);
                }
            }

            DB::commit();
            return $entrega->load('items');
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}