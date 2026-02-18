<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecCodigoVerificacion extends Model
{
    use HasFactory;

    protected $table = 'sec_codigo_verificacion';
    public $timestamps = false; // Custom timestamps

    protected $fillable = [
        'codigo',
        'id_usuario',
        'creado',
        'fecha_activacion',
        'fecha_expiracion',
        'consumido'
    ];

    protected $casts = [
        'creado' => 'datetime',
        'fecha_activacion' => 'datetime',
        'fecha_expiracion' => 'datetime',
        'consumido' => 'boolean'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }
}
