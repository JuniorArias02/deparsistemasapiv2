<?php

namespace App\Modules\GestionCompras\Application\UseCases\Pedidos;

use App\Models\CpPedido;
use App\Models\CpPedidoItem;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Services\PermissionService;

class ObtenerPedidoUseCase
{
    
    

    public function execute($id)
    {
        return CpPedido::with(['items.producto', 'solicitante', 'tipoSolicitud', 'sede', 'elaboradoPor', 'procesoCompra', 'responsableAprobacion', 'creador'])->find($id);
    }
}