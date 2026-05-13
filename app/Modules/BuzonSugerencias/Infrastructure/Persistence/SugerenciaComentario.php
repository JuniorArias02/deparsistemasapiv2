<?php

namespace App\Modules\BuzonSugerencias\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;
use App\Models\Usuario;

class SugerenciaComentario extends Model
{
    protected $table = 'sugerencia_comentarios';
    public $timestamps = false;

    protected $fillable = [
        'sugerencia_id',
        'usuario_id',
        'mensaje',
        'fecha_comentario',
        'leido'
    ];

    protected $casts = [
        'fecha_comentario' => 'datetime',
        'leido' => 'boolean'
    ];

    public function sugerencia()
    {
        return $this->belongsTo(BuzonSugerencia::class, 'sugerencia_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
