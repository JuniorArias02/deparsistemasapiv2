<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CpProveedor extends Model
{
    protected $table = 'cp_proveedores';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'nit',
        'telefono',
        'correo',
        'direccion',
    ];
}
