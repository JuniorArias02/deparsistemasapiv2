<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('usuario.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
}, ['guards' => ['api']]);

Broadcast::channel('buzon.agentes', function ($user) {
    return \Illuminate\Support\Facades\DB::table('rol_permisos')
        ->join('permisos', 'rol_permisos.permiso_id', '=', 'permisos.id')
        ->where('rol_permisos.rol_id', $user->rol_id)
        ->where('permisos.nombre', 'buzon.agente')
        ->exists();
}, ['guards' => ['api']]);

Broadcast::channel('buzon.ticket.{codigo}', function ($user, $codigo) {
    $ticket = \App\Modules\BuzonSugerencias\Infrastructure\Persistence\BuzonSugerencia::where('codigo_ticket', $codigo)->first();
    
    if (!$ticket) {
        return false;
    }
    
    // El creador, el agente asignado, o alguien con el permiso buzon.agente
    if ((int) $ticket->creado_por === (int) $user->id) return true;
    if ((int) $ticket->asignado_a === (int) $user->id) return true;
    
    $isAgente = \Illuminate\Support\Facades\DB::table('rol_permisos')
        ->join('permisos', 'rol_permisos.permiso_id', '=', 'permisos.id')
        ->where('rol_permisos.rol_id', $user->rol_id)
        ->where('permisos.nombre', 'buzon.agente')
        ->exists();
        
    return $isAgente;
}, ['guards' => ['api']]);
