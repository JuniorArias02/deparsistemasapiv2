<?php

namespace App\Modules\BuzonSugerencias\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;

class EstadoTicket extends Model
{
    protected $table = 'estados_ticket';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
    ];
}
