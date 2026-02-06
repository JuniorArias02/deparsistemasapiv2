<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DependenciaSede extends Model
{
    protected $table = 'dependencias_sedes';
    public $timestamps = false; // dependencias_sedes table in migration does not have timestamps

    protected $fillable = [
        'sede_id',
        'nombre',
    ];

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'sede_id');
    }
}
