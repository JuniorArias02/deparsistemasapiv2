<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PcLicenciaSoftware extends Model
{
    protected $table = 'pc_licencias_software';
    public $timestamps = false; 

    protected $fillable = [
        'equipo_id',
        'windows',
        'office',
        'nitro'
    ];

    protected $casts = [
        'equipo_id' => 'integer',
    ];

    public function equipo()
    {
        return $this->belongsTo(PcEquipo::class, 'equipo_id');
    }
}
