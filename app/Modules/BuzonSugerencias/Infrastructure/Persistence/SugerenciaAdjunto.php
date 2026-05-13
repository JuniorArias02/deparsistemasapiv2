<?php

namespace App\Modules\BuzonSugerencias\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;

class SugerenciaAdjunto extends Model
{
    protected $table = 'sugerencia_adjuntos';
    public $timestamps = false;

    protected $fillable = [
        'sugerencia_id',
        'url_imagen',
        'fecha_subida',
    ];

    protected $casts = [
        'fecha_subida' => 'datetime',
    ];

    public function sugerencia()
    {
        return $this->belongsTo(BuzonSugerencia::class, 'sugerencia_id');
    }
}
