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

        // Progreso de Items
        $totalItems = $pedido->items->count();
        $itemsComprados = $pedido->items->where('comprado', 1)->count();
        $porcentajeCompletado = $totalItems > 0 ? round(($itemsComprados / $totalItems) * 100, 1) : 0;

        // Historial de Trazabilidad (Timeline)
        $trazabilidad = [];
        
        if ($pedido->fecha_solicitud) {
            $trazabilidad[] = [
                'hito' => 'Solicitud Creada',
                'fecha' => $pedido->fecha_solicitud->format('Y-m-d H:i:s'),
                'estado' => 'completado'
            ];
        }

        if ($pedido->fecha_solicitud_cotizacion) {
            $trazabilidad[] = [
                'hito' => 'Solicitud de Cotización',
                'fecha' => $pedido->fecha_solicitud_cotizacion,
                'estado' => 'completado'
            ];
        }

        if ($pedido->fecha_respuesta_cotizacion) {
            $trazabilidad[] = [
                'hito' => 'Respuesta de Cotización',
                'fecha' => $pedido->fecha_respuesta_cotizacion,
                'estado' => 'completado'
            ];
        }

        if ($pedido->fecha_compra) {
            $trazabilidad[] = [
                'hito' => 'Revisión de Compras',
                'fecha' => $pedido->fecha_compra->format('Y-m-d H:i:s'),
                'observacion' => $pedido->motivo_aprobacion_compras ?? $pedido->motivo_rechazado_compras,
                'estado' => 'completado'
            ];
        }

        if ($pedido->fecha_gerencia) {
            $trazabilidad[] = [
                'hito' => 'Revisión de Gerencia',
                'fecha' => $pedido->fecha_gerencia->format('Y-m-d H:i:s'),
                'observacion' => $pedido->motivo_aprobacion_gerencia ?? $pedido->motivo_rechazado_gerencia,
                'estado' => 'completado'
            ];
        }

        if ($pedido->fecha_envio_proveedor) {
            $trazabilidad[] = [
                'hito' => 'Envío a Proveedor',
                'fecha' => $pedido->fecha_envio_proveedor,
                'estado' => 'completado'
            ];
        }

        // Ordenar trazabilidad por fecha
        usort($trazabilidad, function ($a, $b) {
            return strtotime($a['fecha']) <=> strtotime($b['fecha']);
        });

        return [
            'pedido_id' => $pedido->id,
            'estado_actual' => $pedido->estado_compras,
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
                        'tiempo_formato' => $tiempoComprasFormato,
                        'motivo' => $pedido->motivo_aprobacion_compras
                    ],
                    'gerencia' => [
                        'responsable' => $pedido->responsableAprobacion ? [
                            'id' => $pedido->responsableAprobacion->id,
                            'nombre' => trim($pedido->responsableAprobacion->nombre_completo),
                            'rol' => 'Gerente'
                        ] : null,
                        'tiempo_horas' => round($tiempoGerenciaHoras, 1),
                        'tiempo_formato' => $tiempoGerenciaFormato,
                        'motivo' => $pedido->motivo_aprobacion_gerencia
                    ]
                ],
                'progreso_items' => [
                    'total' => $totalItems,
                    'comprados_entregados' => $itemsComprados,
                    'pendientes' => $totalItems - $itemsComprados,
                    'porcentaje_completado' => $porcentajeCompletado
                ],
                'tiempo_estimado_entrega_items' => $tiempoMaximoPermitido,
                'reglas_cumplimiento' => [
                    'aprobado_a_tiempo' => $cumpleSla,
                    'entregado_a_tiempo' => $porcentajeCompletado == 100,
                    'dias_retraso' => $diasRetraso,
                    'cumple_sla' => $cumpleSla,
                    'tipo_solicitud' => trim($tipoSolicitud),
                    'categoria' => trim($categoria),
                    'tiempo_maximo_permitido' => $tiempoMaximoPermitido,
                ]
            ],
            'auditoria' => [
                'trazabilidad_eventos' => $trazabilidad,
                'observaciones_generales' => $pedido->observacion,
                'observaciones_pedidos' => $pedido->observaciones_pedidos,
                'observacion_gerencia' => $pedido->observacion_gerencia,
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
