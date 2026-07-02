<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CpPedidoProgramado extends Model
{
    use HasFactory;

    protected $table = 'cp_pedidos_programados';

    protected $fillable = [
        'datos_pedido',
        'fecha_programada',
        'firma_programador',
        'creado_por',
        'estado'
    ];

    protected $casts = [
        'datos_pedido' => 'array',
        'fecha_programada' => 'datetime',
    ];

    public function creador()
    {
        return $this->belongsTo(Usuario::class, 'creado_por');
    }

    public function getFirmaProgramadorAttribute($value)
    {
        if (!$value) return null;
        $path = str_replace(['storage/', 'public/'], '', $value);
        return url('storage/' . $path);
    }
}
