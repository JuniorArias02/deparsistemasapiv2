<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CpProducto extends Model
{
    protected $table = 'cp_productos';
    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'nombre',
    ];
}
