<?php

namespace App\Services;

use App\Models\CpEntregaActivosFijos;
use App\Models\CpEntregaActivosFijosItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;

class CpEntregaActivosFijosService
{
    public function create(array $data, $firmaEntregaFile = null, $firmaRecibeFile = null, $useStoredSignatureEntrega = false, $useStoredSignatureRecibe = false, $user = null)
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

    public function update($id, array $data, $firmaEntregaFile = null, $firmaRecibeFile = null, $useStoredSignatureEntrega = false, $useStoredSignatureRecibe = false, $user = null)
    {
        try {
            DB::beginTransaction();

            $entrega = CpEntregaActivosFijos::findOrFail($id);
            $updateData = $data;

            // Handle file uploads
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
                // Remove existing items
                CpEntregaActivosFijosItem::where('entrega_activos_id', $entrega->id)->delete();
                
                // Add new items
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

    public function delete($id)
    {
        try {
            DB::beginTransaction();

            $entrega = CpEntregaActivosFijos::findOrFail($id);

            // Delete associated items
            CpEntregaActivosFijosItem::where('entrega_activos_id', $id)->delete();

            // Delete signature files if they exist
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

    public function getCoordinadores()
    {
        return \App\Models\Personal::whereIn('id', function ($query) {
            $query->select('coordinador_id')
                ->from('cp_entrega_activos_fijos');
        })->get();
    }

    public function getEntregasPorCoordinador($coordinadorId)
    {
        return CpEntregaActivosFijos::with([
            'personal',
            'sede',
            'procesoSolicitante',
            'coordinador',
            'items.inventario'
        ])
            ->where('coordinador_id', $coordinadorId)
            ->orderBy('id', 'desc')
            ->get();
    }

    public function transferir($id, $nuevoCoordinadorId, $nuevoPersonalId = null)
    {
        DB::beginTransaction();

        try {
            $original = CpEntregaActivosFijos::with('items')->findOrFail($id);

            // Crear la nueva acta duplicada
            $nuevaActa = new CpEntregaActivosFijos();
            $nuevaActa->personal_id = $nuevoPersonalId ?? $original->personal_id;
            $nuevaActa->sede_id = $original->sede_id;
            $nuevaActa->proceso_solicitante = $original->proceso_solicitante;
            $nuevaActa->coordinador_id = $nuevoCoordinadorId;
            $nuevaActa->fecha_entrega = now(); // Fecha actual
            $nuevaActa->firma_quien_entrega = null; // Nuevas firmas en blanco
            $nuevaActa->firma_quien_recibe = null;
            $nuevaActa->save();

            // Duplicar los items
            foreach ($original->items as $item) {
                $nuevoItem = new \App\Models\CpEntregaActivosFijosItem();
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

    public function transferirTodo($coordinadorViejoId, $coordinadorNuevoId)
    {
        $actas = CpEntregaActivosFijos::where('coordinador_id', $coordinadorViejoId)->get();
        
        $transferredCount = 0;
        foreach ($actas as $acta) {
            $this->transferir($acta->id, $coordinadorNuevoId);
            $transferredCount++;
        }

        return $transferredCount;
    }

    protected function handleSignature($file, $useStored, $user, $prefix)
    {
        $path = null;

        if ($useStored && $user) {
            $originalPath = $user->getAttributes()['firma_digital'] ?? null;

            if (!$originalPath || !Storage::disk('public')->exists($originalPath)) {
                throw new Exception('No se encontró una firma digital guardada en su perfil.');
            }

            $extension = pathinfo($originalPath, PATHINFO_EXTENSION);
            $filename = $prefix . '_' . time() . '_stored.' . $extension;
            $newPath = 'entrega_activos_firma/' . $filename;

            Storage::disk('public')->copy($originalPath, $newPath);
            $path = $newPath;
        } elseif ($file) {
            $filename = $prefix . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('entrega_activos_firma', $filename, 'public');
        }

        return $path;
    }
}



