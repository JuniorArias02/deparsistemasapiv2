<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PcDevuelto extends Model
{
    protected $table = 'pc_devuelto';
    public $timestamps = false; 

    protected $fillable = [
        'entrega_id',
        'fecha_devolucion',
        'firma_entrega',
        'firma_recibe',
        'observaciones'
    ];

    protected $casts = [
        'fecha_devolucion' => 'datetime',
        'entrega_id' => 'integer',
    ];

    public function entrega()
    {
        return $this->belongsTo(PcEntrega::class, 'entrega_id');
    }
}
