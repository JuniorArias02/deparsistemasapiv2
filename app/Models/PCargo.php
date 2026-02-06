<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PCargo extends Model
{
    protected $table = 'p_cargo';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
    ];
}
