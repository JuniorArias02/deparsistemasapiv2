<?php

namespace App\Modules\GestionCompras\Application\UseCases\Pedidos;

use App\Models\CpPedido;
use Exception;
use Carbon\Carbon;

class ObtenerEstadisticasPedidoUseCase
{
    public function execute($id)
    {
        $pedido = CpPedido::with(['items', 'tipoSolicitud', 'solicitante', 'responsableAprobacion', 'procesoCompra'])->find($id);

        if (!$pedido) {
            throw new Exception('Pedido no encontrado', 404);
        }

        // Tiempos Individuales
        $tiempoComprasHoras = 0;
        $tiempoComprasFormato = 'N/A';
        if ($pedido->fecha_solicitud && $pedido->fecha_compra) {
            $minutosCompras = $pedido->fecha_solicitud->diffInMinutes($pedido->fecha_compra);
            $tiempoComprasHoras = $minutosCompras / 60;
            $tiempoComprasFormato = $this->formatTime($tiempoComprasHoras);
        }

        $tiempoGerenciaHoras = 0;
        $tiempoGerenciaFormato = 'N/A';
        if ($pedido->fecha_compra && $pedido->fecha_gerencia) {
            $minutosGerencia = $pedido->fecha_compra->diffInMinutes($pedido->fecha_gerencia);
            $tiempoGerenciaHoras = $minutosGerencia / 60;
            $tiempoGerenciaFormato = $this->formatTime($tiempoGerenciaHoras);
        }

        // Tiempos Global de Aprobación
        $tiempoAprobacionHoras = 0;
        $tiempoAprobacionFormato = 'N/A';
        $aprobado = false;
        $fechaAprobacion = $pedido->fecha_gerencia ?: $pedido->fecha_compra;
        
        if ($pedido->fecha_solicitud && $fechaAprobacion) {
            $aprobado = true;
            $minutosTotal = $pedido->fecha_solicitud->diffInMinutes($fechaAprobacion);
            $tiempoAprobacionHoras = $minutosTotal / 60;
            $tiempoAprobacionFormato = $this->formatTime($tiempoAprobacionHoras);
        }

        // SLA
        $tipoSolicitud = $pedido->tipoSolicitud ? $pedido->tipoSolicitud->nombre : 'GENERAL';
        $categoria = $pedido->solicitante ? $pedido->solicitante->nombre : 'General';
        
        $isPrioritaria = stripos($tipoSolicitud, 'prioritari') !== false;
        $isFarmacia = stripos($categoria, 'farmacia') !== false;
        
        $tiempoMaximoHoras = 48; // Default 2 días
        $tiempoMaximoPermitido = '2 días hábiles';

        if ($isPrioritaria) {
            if ($isFarmacia) {
                $tiempoMaximoHoras = 5;
                $tiempoMaximoPermitido = '5 horas';
            } elseif (stripos($categoria, 'nacional') !== false || stripos($tipoSolicitud, 'nacional') !== false) {
                $tiempoMaximoHoras = 96; // 4 días
                $tiempoMaximoPermitido = '3 a 4 días hábiles';
            } else {
                $tiempoMaximoHoras = 48; 
                $tiempoMaximoPermitido = '2 días hábiles';
            }
        } else {
            if ($isFarmacia) {
                $tiempoMaximoHoras = 120; // 5 días
                $tiempoMaximoPermitido = '1 a 5 días';
            } else {
                $tiempoMaximoHoras = 96; // 4 días
                $tiempoMaximoPermitido = '1 a 4 días hábiles';
            }
        }

        $cumpleSla = false;
        $diasRetraso = 0;
        if ($aprobado) {
            $cumpleSla = $tiempoAprobacionHoras <= $tiempoMaximoHoras;
            if (!$cumpleSla) {
                $diasRetraso = round(($tiempoAprobacionHoras - $tiempoMaximoHoras) / 24, 1);
            }
        }

        return [
            'pedido_id' => $pedido->id,
            'estadisticas' => [
                'tiempo_aprobacion_total_horas' => round($tiempoAprobacionHoras, 1),
                'tiempo_aprobacion_total_formato' => $tiempoAprobacionFormato,
                'detalles_aprobacion' => [
                    'compras' => [
                        'responsable' => $pedido->procesoCompra ? [
                            'id' => $pedido->procesoCompra->id,
                            'nombre' => trim($pedido->procesoCompra->nombre_completo),
                            'rol' => 'Compras'
                        ] : null,
                        'tiempo_horas' => round($tiempoComprasHoras, 1),
                        'tiempo_formato' => $tiempoComprasFormato
                    ],
                    'gerencia' => [
                        'responsable' => $pedido->responsableAprobacion ? [
                            'id' => $pedido->responsableAprobacion->id,
                            'nombre' => trim($pedido->responsableAprobacion->nombre_completo),
                            'rol' => 'Gerente'
                        ] : null,
                        'tiempo_horas' => round($tiempoGerenciaHoras, 1),
                        'tiempo_formato' => $tiempoGerenciaFormato
                    ]
                ],
                'tiempo_estimado_entrega_items' => $tiempoMaximoPermitido,
                'reglas_cumplimiento' => [
                    'aprobado_a_tiempo' => $cumpleSla,
                    'entregado_a_tiempo' => false,
                    'dias_retraso' => $diasRetraso,
                    'cumple_sla' => $cumpleSla,
                    'tipo_solicitud' => trim($tipoSolicitud),
                    'categoria' => trim($categoria),
                    'tiempo_maximo_permitido' => $tiempoMaximoPermitido,
                ]
            ]
        ];
    }

    private function formatTime($horasDecimal)
    {
        $dias = floor($horasDecimal / 24);
        $horasRestantes = $horasDecimal - ($dias * 24);
        if ($dias > 0) {
            return "{$dias} días y " . round($horasRestantes, 1) . " horas";
        }
        return round($horasRestantes, 1) . " horas";
    }
}
