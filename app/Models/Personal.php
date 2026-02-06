<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Personal extends Model
{
    protected $table = 'personal';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'cedula',
        'telefono',
        'cargo_id',
    ];

    public function cargo()
    {
        return $this->belongsTo(PCargo::class, 'cargo_id');
    }
}
