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

class ActualizarItemsPedidoUseCase
{
    
    

    public function execute($id, array $items)
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
}