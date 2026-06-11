<?php

namespace App\Modules\GestionCompras\Application\UseCases\EntregaActivosFijos;

use App\Models\CpEntregaActivosFijos;
use App\Models\CpEntregaActivosFijosItem;
use Illuminate\Support\Facades\DB;
use Exception;

class CrearEntregaActivosFijosUseCase
{
    use HandleSignatureTrait;

    public function execute(array $data, $firmaEntregaFile = null, $firmaRecibeFile = null, $useStoredSignatureEntrega = false, $useStoredSignatureRecibe = false, $user = null)
    {
        try {
            DB::beginTransaction();

            $firmaEntregaPath = $this->handleSignature($firmaEntregaFile, $useStoredSignatureEntrega, $user, 'entrega_firma_entrega');
            if ($firmaEntregaPath) {
                $firmaEntregaPath = 'storage/' . $firmaEntregaPath;
            }

            $firmaRecibePath = $this->handleSignature($firmaRecibeFile, $useStoredSignatureRecibe, $user, 'entrega_firma_recibe');
            if ($firmaRecibePath) {
                $firmaRecibePath = 'storage/' . $firmaRecibePath;
            }

            $entrega = CpEntregaActivosFijos::create([
                'personal_id' => $data['personal_id'],
                'sede_id' => $data['sede_id'],
                'proceso_solicitante' => $data['proceso_solicitante'],
                'coordinador_id' => $data['coordinador_id'],
                'fecha_entrega' => $data['fecha_entrega'],
                'firma_quien_entrega' => $firmaEntregaPath,
                'firma_quien_recibe' => $firmaRecibePath,
            ]);

            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
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