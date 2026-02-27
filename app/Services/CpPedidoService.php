<?php

namespace App\Services;

use App\Models\CpPedido;
use App\Models\CpItemPedido;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

class CpPedidoService
{
    public function __construct(protected PermissionService $permissionService) {}

    public function getAll(Usuario $user, array $filters = [])
    {
        $query = CpPedido::with(['items.producto', 'solicitante', 'tipoSolicitud', 'sede', 'elaboradoPor', 'procesoCompra', 'responsableAprobacion', 'creador'])
            ->orderBy('id', 'desc');

        if (!empty($filters['consecutivo'])) {
            $query->where('consecutivo', $filters['consecutivo']);
        } elseif (!empty($filters['month'])) {
            $query->where('fecha_solicitud', 'like', $filters['month'] . '%');
        }
        if ($this->permissionService->check($user, 'cp_pedido.listar.compras')) {
            return $query->get();
        } elseif ($this->permissionService->check($user, 'cp_pedido.listar.responsable')) {
            return $query->where('estado_compras', 'aprobado')->get();
        } else {
            return $query->where('creador_por', $user->id)->get();
        }
    }

    public function getById($id)
    {
        return CpPedido::with(['items.producto', 'solicitante', 'tipoSolicitud', 'sede', 'elaboradoPor', 'procesoCompra', 'responsableAprobacion', 'creador'])->find($id);
    }

    public function create(array $data, $firmaFile = null, $useStoredSignature = false, Usuario $user)
    {
        DB::beginTransaction();

        try {
            $path = $this->handleSignature($firmaFile, $useStoredSignature, $user, 'elaboracion');

            if (empty($path)) {
                throw new Exception('La firma es obligatoria para crear un pedido.');
            }

            // Calculate consecutivo
            $lastConsecutivo = CpPedido::max('consecutivo');
            $nextConsecutivo = $lastConsecutivo ? $lastConsecutivo + 1 : 1;

            /** @var CpPedido $pedido */
            $pedido = CpPedido::create([
                'estado_compras' => 'pendiente',
                'fecha_solicitud' => now(),
                'proceso_solicitante' => $data['proceso_solicitante'],
                'tipo_solicitud' => $data['tipo_solicitud'],
                'consecutivo' => $nextConsecutivo,
                'observacion' => $data['observacion'] ?? null,
                'sede_id' => $data['sede_id'],
                'elaborado_por' => $data['elaborado_por'],
                'elaborado_por_firma' => 'storage/' . $path,
                'creador_por' => $user->id,
                'pedido_visto' => 0,
                'estado_gerencia' => 'pendiente',
            ]);

            foreach ($data['items'] as $item) {
                CpItemPedido::create([
                    'nombre' => $item['nombre'],
                    'cantidad' => $item['cantidad'],
                    'unidad_medida' => $item['unidad_medida'],
                    'referencia_items' => $item['referencia_items'] ?? null,
                    'cp_pedido' => $pedido->id,
                    'productos_id' => $item['productos_id'],
                    'comprado' => 0,
                ]);
            }

            DB::commit();

            $pedido->load(['items', 'solicitante', 'tipoSolicitud', 'sede', 'elaboradoPor', 'creador']);
            $this->sendNewOrderNotification($pedido);

            return $pedido->load('items');
        } catch (Exception $e) {
            DB::rollBack();
            if (isset($path) && strpos($path, 'stored') === false) {
                Storage::disk('public')->delete($path);
            }
            throw $e;
        }
    }

    public function update($id, array $data, $firmaFile = null, $useStoredSignature = false, Usuario $user)
    {
        $pedido = CpPedido::findOrFail($id);

        DB::beginTransaction();

        try {
            $updateData = [
                'proceso_solicitante' => $data['proceso_solicitante'],
                'tipo_solicitud' => $data['tipo_solicitud'],
                'observacion' => $data['observacion'] ?? null,
                'sede_id' => $data['sede_id'],
                'elaborado_por' => $data['elaborado_por'],
            ];

            // Only update signature if provided or requested to use stored
            if ($firmaFile || $useStoredSignature) {
                $path = $this->handleSignature($firmaFile, $useStoredSignature, $user, 'elaboracion_edit');
                if ($path) {
                    $updateData['elaborado_por_firma'] = 'storage/' . $path;
                }
            }

            $pedido->update($updateData);

            // Sync items: Delete and recreate
            $pedido->items()->delete();

            foreach ($data['items'] as $item) {
                CpItemPedido::create([
                    'nombre' => $item['nombre'],
                    'cantidad' => $item['cantidad'],
                    'unidad_medida' => $item['unidad_medida'],
                    'referencia_items' => $item['referencia_items'] ?? null,
                    'cp_pedido' => $pedido->id,
                    'productos_id' => $item['productos_id'],
                    'comprado' => 0,
                ]);
            }

            DB::commit();

            return $pedido->load('items');
        } catch (Exception $e) {
            DB::rollBack();
            if (isset($path) && strpos($path, 'stored') === false) {
                Storage::disk('public')->delete($path);
            }
            throw $e;
        }
    }

    public function delete($id)
    {
        $pedido = CpPedido::findOrFail($id);

        // Safety check: Only allow deletion if it hasn't been processed
        if ($pedido->estado_compras !== 'pendiente' || $pedido->estado_gerencia !== 'pendiente') {
            throw new Exception('No se puede eliminar un pedido que ya ha sido procesado (aprobado o rechazado).');
        }

        DB::beginTransaction();
        try {
            // 1. Delete items first
            $pedido->items()->delete();

            // 2. Cleanup signature file if it's not a profile-stored signature
            // Signature paths usually look like 'storage/pedidos_firma/...' or 'storage/stored_firma/...'
            if ($pedido->elaborado_por_firma) {
                $relativePath = str_replace('storage/', '', $pedido->elaborado_por_firma);
                // Only delete if it's in the pedidos_firma folder and not a 'stored' one
                if (strpos($relativePath, 'pedidos_firma/') === 0 && strpos($relativePath, '_stored.') === false) {
                    if (Storage::disk('public')->exists($relativePath)) {
                        Storage::disk('public')->delete($relativePath);
                    }
                }
            }

            // 3. Delete the pedido record
            $pedido->delete();

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function aprobarCompras($id, array $data, $firmaFile = null, $useStoredSignature = false, Usuario $user)
    {
        $pedido = CpPedido::find($id);
        if (!$pedido) {
            throw new Exception('Pedido no encontrado', 404);
        }

        $path = $this->handleSignature($firmaFile, $useStoredSignature, $user, 'compra');

        if (empty($path)) {
            throw new Exception('La firma es obligatoria para aprobar el pedido en compras.');
        }

        $pedido->update([
            'estado_compras' => 'aprobado',
            'proceso_compra' => $user->id,
            'proceso_compra_firma' => 'storage/' . $path,
            'motivo_aprobacion_compras' => $data['motivo_aprobacion_compras'] ?? null,
            'fecha_compra' => now(),
        ]);

        if (isset($data['items_comprados']) && is_array($data['items_comprados'])) {
            CpItemPedido::whereIn('id', $data['items_comprados'])->update(['comprado' => 1]);
        }

        $this->sendOrderApprovedNotification($pedido);

        return $pedido->load('items');
    }

    public function rechazarCompras($id, $motivo)
    {
        $pedido = CpPedido::find($id);
        if (!$pedido) {
            throw new Exception('Pedido no encontrado', 404);
        }

        $pedido->update([
            'estado_compras' => 'rechazado',
            'motivo_rechazado_compras' => $motivo
        ]);

        $this->sendOrderRejectedNotification($pedido, $motivo);

        return $pedido;
    }

    public function aprobarGerencia($id, array $data, $firmaFile = null, $useStoredSignature = false, Usuario $user)
    {
        $pedido = CpPedido::find($id);
        if (!$pedido) {
            throw new Exception('Pedido no encontrado', 404);
        }

        $path = $this->handleSignature($firmaFile, $useStoredSignature, $user, 'gerencia');

        if (empty($path)) {
            throw new Exception('La firma es obligatoria para aprobar el pedido en gerencia.');
        }

        $pedido->update([
            'estado_gerencia' => 'aprobado',
            'responsable_aprobacion' => $user->id,
            'responsable_aprobacion_firma' => 'storage/' . $path,
            'fecha_gerencia' => now(),
            'motivo_aprobacion_gerencia' => $data['motivo_aprobacion_gerencia'] ?? null,
        ]);

        $this->sendGerenciaApprovedNotification($pedido);

        return $pedido;
    }

    public function rechazarGerencia($id, $motivo, Usuario $user)
    {
        $pedido = CpPedido::find($id);
        if (!$pedido) {
            throw new Exception('Pedido no encontrado', 404);
        }

        $pedido->update([
            'estado_gerencia' => 'rechazado',
            'responsable_aprobacion' => $user->id,
            'motivo_rechazado_gerencia' => $motivo,
        ]);

        $this->sendGerenciaRejectedNotification($pedido, $motivo);

        return $pedido;
    }

    public function updateItems($id, array $items)
    {
        $pedido = CpPedido::find($id);
        if (!$pedido) {
            throw new Exception('Pedido no encontrado', 404);
        }

        foreach ($items as $itemData) {
            CpItemPedido::where('id', $itemData['id'])
                ->where('cp_pedido', $id)
                ->update(['comprado' => $itemData['comprado']]);
        }

        return $pedido->load('items');
    }

    // Helper methods

    protected function handleSignature($file, $useStored, $user, $prefix)
    {
        $path = null;

        if ($useStored) {
            $originalPath = $user->getAttributes()['firma_digital'] ?? null;

            if (!$originalPath || !Storage::disk('public')->exists($originalPath)) {
                throw new Exception('No se encontró una firma digital guardada en su perfil.');
            }

            $extension = pathinfo($originalPath, PATHINFO_EXTENSION);
            $filename = $prefix . '_' . time() . '_stored.' . $extension;
            $newPath = 'pedidos_firma/' . $filename;

            Storage::disk('public')->copy($originalPath, $newPath);
            $path = $newPath;
        } elseif ($file) {
            $filename = $prefix . '_' . time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('pedidos_firma', $filename, 'public');
        }

        return $path;
    }

    protected function sendNewOrderNotification(CpPedido $pedido)
    {
        try {
            $comprasUsers = Usuario::whereHas('rol.permisos', function ($query) {
                $query->where('nombre', 'cp_pedido.listar.compras');
            })->whereNotNull('correo')->get();

            foreach ($comprasUsers as $user) {
                Mail::to($user->correo)->send(new \App\Mail\NewOrderNotification($pedido));
            }
        } catch (Exception $e) {
            Log::error('Error enviando correo de nuevo pedido a compras: ' . $e->getMessage());
        }
    }

    protected function sendOrderApprovedNotification(CpPedido $pedido)
    {
        try {
            if ($pedido->creador && $pedido->creador->correo) {
                Mail::to($pedido->creador->correo)->send(new \App\Mail\OrderApprovedNotification($pedido));
            }
        } catch (Exception $e) {
            Log::error('Error enviando correo de aprobación de pedido: ' . $e->getMessage());
        }
    }

    protected function sendOrderRejectedNotification(CpPedido $pedido, $motivo)
    {
        try {
            if ($pedido->creador && $pedido->creador->correo) {
                Mail::to($pedido->creador->correo)->send(new \App\Mail\OrderRejectedNotification($pedido, $motivo));
            }
        } catch (Exception $e) {
            Log::error('Error enviando correo de rechazo de pedido: ' . $e->getMessage());
        }
    }

    protected function sendGerenciaApprovedNotification(CpPedido $pedido)
    {
        try {
            if ($pedido->creador && $pedido->creador->correo) {
                Mail::to($pedido->creador->correo)->send(new \App\Mail\GerenciaApprovedNotification($pedido));
            }
        } catch (Exception $e) {
            Log::error('Error enviando correo de aprobación gerencia: ' . $e->getMessage());
        }
    }

    protected function sendGerenciaRejectedNotification(CpPedido $pedido, $motivo)
    {
        try {
            if ($pedido->creador && $pedido->creador->correo) {
                Mail::to($pedido->creador->correo)->send(new \App\Mail\GerenciaRejectedNotification($pedido, $motivo));
            }
        } catch (Exception $e) {
            Log::error('Error enviando correo de rechazo de gerencia: ' . $e->getMessage());
        }
    }
}
