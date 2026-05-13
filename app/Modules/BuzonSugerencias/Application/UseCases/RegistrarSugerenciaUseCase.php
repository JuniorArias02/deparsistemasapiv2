<?php

namespace App\Modules\BuzonSugerencias\Application\UseCases;

use App\Models\Usuario;
use App\Modules\BuzonSugerencias\Infrastructure\Persistence\BuzonSugerencia;
use App\Modules\BuzonSugerencias\Infrastructure\Persistence\EstadoTicket;
use App\Mail\BuzonSugerencias\NuevoTicketCreadorMail;
use App\Mail\BuzonSugerencias\NuevoTicketAgenteMail;
use Illuminate\Support\Facades\Mail;

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

        // Cargar relaciones necesarias para el correo
        $sugerencia->load(['creador', 'estado']);

        // 1. Notificar al creador del ticket
        if ($sugerencia->creador && $sugerencia->creador->correo) {
            try {
                Mail::to($sugerencia->creador->correo)->send(new NuevoTicketCreadorMail($sugerencia));
            } catch (\Exception $e) {
                \Log::error("Error enviando correo al creador: " . $e->getMessage());
            }
        }

        // 2. Notificar a los agentes (usuarios con permiso 'buzon.agente')
        $agentes = Usuario::whereHas('rol.permisos', function ($query) {
            $query->where('nombre', 'buzon.agente');
        })->where('estado', 1)->get();

        foreach ($agentes as $agente) {
            if ($agente->correo) {
                try {
                    Mail::to($agente->correo)->send(new NuevoTicketAgenteMail($sugerencia));
                } catch (\Exception $e) {
                    \Log::error("Error enviando correo al agente {$agente->correo}: " . $e->getMessage());
                }
            }
        }

        return $sugerencia;
    }
}
