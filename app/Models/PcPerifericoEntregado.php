<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PcPerifericoEntregado extends Model
{
    protected $table = 'pc_perifericos_entregados';
    public $timestamps = false; 

    protected $fillable = [
        'entrega_id',
        'inventario_id',
        'cantidad',
        'observaciones'
    ];

    protected $casts = [
        'entrega_id' => 'integer',
        'inventario_id' => 'integer',
        'cantidad' => 'integer',
    ];

    public function entrega()
    {
        return $this->belongsTo(PcEntrega::class, 'entrega_id');
    }

    public function inventario()
    {
        return $this->belongsTo(Inventario::class, 'inventario_id');
    }
}
