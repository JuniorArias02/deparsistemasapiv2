<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CpTipoSolicitud extends Model
{
    protected $table = 'cp_tipo_solicitud';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
    ];
}
