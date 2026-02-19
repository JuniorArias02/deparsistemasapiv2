<?php

namespace App\Services;

use App\Models\CpEntregaActivosFijos;
use App\Models\CpEntregaActivosFijosItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;

class CpEntregaActivosFijosService
{
    public function create(array $data, $firmaEntregaFile = null, $firmaRecibeFile = null)
    {
        try {
            DB::beginTransaction();

            // Handle file uploads
            $firmaEntregaPath = null;
            $firmaRecibePath = null;

            if ($firmaEntregaFile) {
                $filename = time() . '_firma_entrega.' . $firmaEntregaFile->getClientOriginalExtension();
                $path = $firmaEntregaFile->storeAs('entrega_activos_firma', $filename, 'public');
                $firmaEntregaPath = 'storage/' . $path;
            }

            if ($firmaRecibeFile) {
                $filename = time() . '_firma_recibe.' . $firmaRecibeFile->getClientOriginalExtension();
                $path = $firmaRecibeFile->storeAs('entrega_activos_firma', $filename, 'public');
                $firmaRecibePath = 'storage/' . $path;
            }

            /** @var CpEntregaActivosFijos $entrega */
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

    public function update($id, array $data, $firmaEntregaFile = null, $firmaRecibeFile = null)
    {
        try {
            DB::beginTransaction();

            $entrega = CpEntregaActivosFijos::findOrFail($id);
            $updateData = $data;

            // Handle file uploads
            if ($firmaEntregaFile) {
                // Delete old file if exists
                if ($entrega->firma_quien_entrega) {
                    Storage::disk('public')->delete(str_replace('storage/', '', $entrega->firma_quien_entrega));
                }
                $filename = time() . '_firma_entrega.' . $firmaEntregaFile->getClientOriginalExtension();
                $path = $firmaEntregaFile->storeAs('entrega_activos_firma', $filename, 'public');
                $updateData['firma_quien_entrega'] = 'storage/' . $path;
            }

            if ($firmaRecibeFile) {
                // Delete old file if exists
                if ($entrega->firma_quien_recibe) {
                    Storage::disk('public')->delete(str_replace('storage/', '', $entrega->firma_quien_recibe));
                }
                $filename = time() . '_firma_recibe.' . $firmaRecibeFile->getClientOriginalExtension();
                $path = $firmaRecibeFile->storeAs('entrega_activos_firma', $filename, 'public');
                $updateData['firma_quien_recibe'] = 'storage/' . $path;
            }

            if (isset($updateData['items'])) {
                unset($updateData['items']);
            }

            $entrega->update($updateData);

            DB::commit();
            return $entrega->load('items');
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete($id)
    {
        try {
            DB::beginTransaction();

            $entrega = CpEntregaActivosFijos::findOrFail($id);

            // Delete associated items
            CpEntregaActivosFijosItem::where('entrega_activos_id', $id)->delete();

            // Delete signature files if they exist
            if ($entrega->firma_quien_entrega) {
                Storage::disk('public')->delete(str_replace('storage/', '', $entrega->firma_quien_entrega));
            }
            if ($entrega->firma_quien_recibe) {
                Storage::disk('public')->delete(str_replace('storage/', '', $entrega->firma_quien_recibe));
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
