<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgendaMantenimiento extends Model
{
    protected $table = 'agenda_mantenimientos';
    public $timestamps = false;

    protected $fillable = [
        'mantenimiento_id',
        'titulo',
        'descripcion',
        'sede_id',
        'fecha_inicio',
        'fecha_fin',
        'creado_por',
        'agendado_por',
        'fecha_creacion',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'fecha_creacion' => 'datetime',
    ];

    // Relationships

    public function mantenimiento()
    {
        return $this->belongsTo(Mantenimiento::class, 'mantenimiento_id');
    }

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'sede_id');
    }

    public function creador()
    {
        return $this->belongsTo(Usuario::class, 'creado_por');
    }

    public function agendador()
    {
        return $this->belongsTo(Usuario::class, 'agendado_por');
    }
}
