<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $table = 'areas';
    public $timestamps = false; // No timestamps in migration

    protected $fillable = [
        'nombre',
        'sede_id'
    ];

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'sede_id');
    }
}
