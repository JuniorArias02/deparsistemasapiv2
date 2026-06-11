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

class CalcularTiempoEntregaPedidoUseCase
{
    
    

    public function execute(CpPedido $pedido){
        $tiempoCompras = null;
        $tiempoGerencia = null;
        $tiempoTotal = null;

        if ($pedido->fecha_solicitud && $pedido->fecha_compra) {
            $tiempoCompras = $pedido->fecha_solicitud->diffInSeconds($pedido->fecha_compra);
        }

        if ($pedido->fecha_compra && $pedido->fecha_gerencia) {
            $tiempoGerencia = $pedido->fecha_compra->diffInSeconds($pedido->fecha_gerencia);
        }

        if ($pedido->fecha_solicitud && $pedido->fecha_gerencia) {
            $tiempoTotal = $pedido->fecha_solicitud->diffInSeconds($pedido->fecha_gerencia);
        }

        return [
            'tiempo_compras' => $tiempoCompras !== null ? $this->formatSeconds($tiempoCompras) : 'N/A',
            'tiempo_compras_segundos' => $tiempoCompras,
            'tiempo_gerencia' => $tiempoGerencia !== null ? $this->formatSeconds($tiempoGerencia) : 'N/A',
            'tiempo_gerencia_segundos' => $tiempoGerencia,
            'tiempo_total' => $tiempoTotal !== null ? $this->formatSeconds($tiempoTotal) : 'N/A',
            'tiempo_total_segundos' => $tiempoTotal,
        ];
    }
}