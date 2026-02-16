<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PcCaracteristicasTecnicas extends Model
{
    protected $table = 'pc_caracteristicas_tecnicas';
    public $timestamps = false; // No timestamps in schema

    protected $fillable = [
        'equipo_id',
        'procesador',
        'memoria_ram',
        'disco_duro',
        'tarjeta_video',
        'tarjeta_red',
        'tarjeta_sonido',
        'usb',
        'unidad_cd',
        'parlantes',
        'drive',
        'monitor',
        'monitor_id',
        'teclado',
        'teclado_id',
        'mouse',
        'mouse_id',
        'internet',
        'velocidad_red',
        'capacidad_disco'
    ];

    protected $casts = [
        'equipo_id' => 'integer',
        'monitor_id' => 'integer',
        'teclado_id' => 'integer',
        'mouse_id' => 'integer',
    ];

    public function equipo()
    {
        return $this->belongsTo(PcEquipo::class, 'equipo_id');
    }

    public function monitorInventario()
    {
        return $this->belongsTo(Inventario::class, 'monitor_id');
    }

    public function tecladoInventario()
    {
        return $this->belongsTo(Inventario::class, 'teclado_id');
    }

    public function mouseInventario()
    {
        return $this->belongsTo(Inventario::class, 'mouse_id');
    }
}
