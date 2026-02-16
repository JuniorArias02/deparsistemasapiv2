<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CpEntregaActivosFijosItem extends Model
{
    use HasFactory;

    protected $table = 'cp_entrega_activos_fijos_items';
    public $timestamps = false;

    protected $fillable = [
        'item_id',
        'es_accesorio',
        'accesorio_descripcion',
        'entrega_activos_id',
    ];

    protected $casts = [
        'es_accesorio' => 'boolean',
    ];

    // Relationships
    public function entrega()
    {
        return $this->belongsTo(CpEntregaActivosFijos::class, 'entrega_activos_id');
    }

    public function inventario()
    {
        return $this->belongsTo(Inventario::class, 'item_id');
    }
}
