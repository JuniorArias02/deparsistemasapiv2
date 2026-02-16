<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DatosEmpresa extends Model
{
    protected $table = 'datos_empresa';
    public $timestamps = false; 

    protected $fillable = [
        'nombre',
        'nit',
        'direccion',
        'telefono',
        'email',
        'representante_legal',
        'ciudad'
    ];
}
