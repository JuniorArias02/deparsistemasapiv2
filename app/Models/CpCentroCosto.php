<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CpCentroCosto extends Model
{
    use HasFactory;

    protected $table = 'cp_centro_costo';
    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'nombre'
    ];
}
