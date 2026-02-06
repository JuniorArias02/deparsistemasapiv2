<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sede extends Model
{
    protected $table = 'sedes';
    public $timestamps = false; // sedes table in migration does not have timestamps
    protected $fillable = ['nombre'];
}
