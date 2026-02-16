<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PcConfigCronograma extends Model
{
    protected $table = 'pc_config_cronograma';
    public $timestamps = false;

    protected $fillable = [
        'dias_cumplimiento',
        'meses_cumplimiento',
    ];

    protected $casts = [
        'dias_cumplimiento' => 'integer',
        'meses_cumplimiento' => 'integer',
    ];
}
