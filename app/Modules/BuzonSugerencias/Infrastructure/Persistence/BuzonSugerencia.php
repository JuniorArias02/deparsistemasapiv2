<?php

namespace App\Modules\BuzonSugerencias\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;
use App\Models\Usuario;

class BuzonSugerencia extends Model
{
    protected $table = 'buzon_sugerencia';
    public $timestamps = false; // We use custom timestamps

    protected $fillable = [
        'codigo_ticket',
        'asunto',
        'observaciones',
        'estado_id',
        'prioridad',
        'creado_por',
        'asignado_a',
        'fecha_creacion',
        'fecha_cierre'
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'fecha_cierre' => 'datetime',
    ];

    public function estado()
    {
        return $this->belongsTo(EstadoTicket::class, 'estado_id');
    }

    public function creador()
    {
        return $this->belongsTo(Usuario::class, 'creado_por');
    }

    public function asignado()
    {
        return $this->belongsTo(Usuario::class, 'asignado_a');
    }

    public function adjuntos()
    {
        return $this->hasMany(SugerenciaAdjunto::class, 'sugerencia_id');
    }

    public function comentarios()
    {
        return $this->hasMany(SugerenciaComentario::class, 'sugerencia_id');
    }
}
