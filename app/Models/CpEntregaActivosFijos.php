<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CpEntregaActivosFijos extends Model
{
    use HasFactory;

    protected $table = 'cp_entrega_activos_fijos';
    public $timestamps = false;

    protected $fillable = [
        'personal_id',
        'sede_id',
        'proceso_solicitante',
        'coordinador_id',
        'fecha_entrega',
        'firma_quien_entrega',
        'firma_quien_recibe',
    ];

    protected $casts = [
        'fecha_entrega' => 'date',
    ];

    // Relationships
    public function personal()
    {
        return $this->belongsTo(Personal::class, 'personal_id');
    }

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'sede_id');
    }

    public function procesoSolicitante()
    {
        return $this->belongsTo(DependenciaSede::class, 'proceso_solicitante');
    }

    public function coordinador()
    {
        return $this->belongsTo(Personal::class, 'coordinador_id');
    }

    public function items()
    {
        return $this->hasMany(CpEntregaActivosFijosItem::class, 'entrega_activos_id');
    }
}
