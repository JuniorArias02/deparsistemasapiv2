<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CpDependencia extends Model
{
    protected $table = 'cp_dependencias';
    public $timestamps = false; 

    protected $fillable = [
        'codigo',
        'nombre',
        'sede_id'
    ];

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'sede_id');
    }
}



