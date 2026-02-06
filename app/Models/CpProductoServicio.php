<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CpProductoServicio extends Model
{
    protected $table = 'cp_productos_servicios';
    public $timestamps = false;

    protected $fillable = [
        'codigo_producto',
        'nombre',
    ];
}
