<?php

namespace App\Modules\GestionCompras\Application\UseCases\Pedidos;

use App\Models\CpPedido;
use App\Models\CpItemPedido;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Services\PermissionService;

class EliminarPedidoUseCase
{
    
    

    public function execute($id)
    {
        $pedido = CpPedido::findOrFail($id);

        if ($pedido->estado_compras !== 'pendiente' || $pedido->estado_gerencia !== 'pendiente') {
            throw new Exception('No se puede eliminar un pedido que ya ha sido procesado (aprobado o rechazado).');
        }

        DB::beginTransaction();
        try {
            // 1. Delete items first
            $pedido->items()->delete();
            if ($pedido->elaborado_por_firma) {
                $relativePath = str_replace('storage/', '', $pedido->elaborado_por_firma);
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
}