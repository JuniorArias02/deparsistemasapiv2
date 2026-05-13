<?php

namespace App\Modules\BuzonSugerencias\Application\UseCases;

use App\Modules\BuzonSugerencias\Infrastructure\Persistence\BuzonSugerencia;
use App\Modules\BuzonSugerencias\Infrastructure\Persistence\EstadoTicket;

class RegistrarSugerenciaUseCase
{
    public function execute(array $data)
    {
        $estado = EstadoTicket::where('nombre', 'Abierto')->first();
        
        $year = date('Y');
        $lastTicket = BuzonSugerencia::whereYear('fecha_creacion', $year)->orderBy('id', 'desc')->first();
        $nextNumber = $lastTicket ? intval(substr($lastTicket->codigo_ticket, -3)) + 1 : 1;
        $codigoTicket = sprintf("SUG-%s-%03d", $year, $nextNumber);

        $sugerencia = BuzonSugerencia::create([
            'codigo_ticket' => $codigoTicket,
            'asunto' => $data['asunto'],
            'observaciones' => $data['observaciones'],
            'prioridad' => $data['prioridad'] ?? 'Baja',
            'creado_por' => $data['creado_por'],
            'estado_id' => $estado->id,
            'fecha_creacion' => now(),
        ]);

        return $sugerencia;
    }
}
