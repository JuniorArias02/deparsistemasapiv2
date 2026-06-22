<?php

namespace App\Modules\GestionCompras\Application\UseCases\Pedidos;

use App\Models\CpPedido;
use Exception;
use Carbon\Carbon;

class ObtenerEstadisticasPedidoUseCase
{
    public function execute($id)
    {
        $pedido = CpPedido::with(['items', 'tipoSolicitud', 'solicitante', 'responsableAprobacion'])->find($id);

        if (!$pedido) {
            throw new Exception('Pedido no encontrado', 404);
        }

        // Determinar Tiempos de Aprobación
        $tiempoAprobacionHoras = 0;
        $tiempoAprobacionFormato = 'N/A';
        $aprobado = false;
        
        // Asumiendo que la aprobación final la da Gerencia o Compras
        $fechaAprobacion = $pedido->fecha_gerencia ?: $pedido->fecha_compra;
        
        if ($pedido->fecha_solicitud && $fechaAprobacion) {
            $aprobado = true;
            // diffInMinutes para mayor precisión antes de pasar a horas
            $minutos = $pedido->fecha_solicitud->diffInMinutes($fechaAprobacion);
            $tiempoAprobacionHoras = $minutos / 60;
            
            $dias = floor($tiempoAprobacionHoras / 24);
            $horasRestantes = $tiempoAprobacionHoras - ($dias * 24);
            
            if ($dias > 0) {
                $tiempoAprobacionFormato = "{$dias} días y " . round($horasRestantes, 1) . " horas";
            } else {
                $tiempoAprobacionFormato = round($horasRestantes, 1) . " horas";
            }
        }

        // Determinar SLA según Reglas
        $tipoSolicitud = $pedido->tipoSolicitud ? $pedido->tipoSolicitud->nombre : 'GENERAL';
        $categoria = $pedido->solicitante ? $pedido->solicitante->nombre : 'General';
        
        $isPrioritaria = stripos($tipoSolicitud, 'prioritari') !== false;
        $isFarmacia = stripos($categoria, 'farmacia') !== false;
        
        $tiempoMaximoHoras = 48; // Por defecto 2 días hábiles (48h)
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
            // Recurrente u otras
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
                'tiempo_aprobacion_horas' => round($tiempoAprobacionHoras, 1),
                'tiempo_aprobacion_formato' => $tiempoAprobacionFormato,
                'responsable_aprobacion' => $pedido->responsableAprobacion ? [
                    'id' => $pedido->responsableAprobacion->id,
                    'nombre' => trim($pedido->responsableAprobacion->nombres . ' ' . $pedido->responsableAprobacion->apellidos),
                    'rol' => 'Gerente'
                ] : null,
                'tiempo_estimado_entrega_items' => $tiempoMaximoPermitido,
                'reglas_cumplimiento' => [
                    'aprobado_a_tiempo' => $cumpleSla,
                    'entregado_a_tiempo' => false, // Aquí se podría evaluar fecha_entregado de los items si es necesario
                    'dias_retraso' => $diasRetraso,
                    'cumple_sla' => $cumpleSla,
                    'tipo_solicitud' => $tipoSolicitud,
                    'categoria' => $categoria,
                    'tiempo_maximo_permitido' => $tiempoMaximoPermitido,
                ]
            ]
        ];
    }
}
