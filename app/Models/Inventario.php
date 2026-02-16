<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
    protected $table = 'inventario';
    public $timestamps = false; // Custom timestamps in migration (fecha_creacion, fecha_actualizacion)

    protected $fillable = [
        'codigo',
        'nombre',
        'dependencia',
        'responsable',
        'responsable_id',
        'coordinador_id',
        'marca',
        'modelo',
        'serial',
        'proceso_id',
        'sede_id',
        'creado_por',
        'fecha_creacion',
        'codigo_barras',
        'num_factu',
        'grupo',
        'vida_util',
        'vida_util_niff',
        'centro_costo',
        'ubicacion',
        'proveedor',
        'fecha_compra',
        'soporte',
        'soporte_adjunto',
        'descripcion',
        'estado',
        'escritura',
        'matricula',
        'valor_compra',
        'salvamenta',
        'depreciacion',
        'depreciacion_niif',
        'meses',
        'meses_niif',
        'tipo_adquisicion',
        'calibrado',
        'observaciones',
        'fecha_actualizacion',
        'cuenta_inventario',
        'cuenta_gasto',
        'cuenta_salida',
        'grupo_activos',
        'valor_actual',
        'depreciacion_acumulada',
        'tipo_bien',
        'tiene_accesorio',
        'descripcion_accesorio'
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'fecha_compra' => 'date',
        'calibrado' => 'date',
        'fecha_actualizacion' => 'datetime',
    ];

    public function creador()
    {
        return $this->belongsTo(Usuario::class, 'creado_por');
    }

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'sede_id');
    }

    public function responsablePersonal()
    {
        return $this->belongsTo(Personal::class, 'responsable_id');
    }

    public function coordinadorPersonal()
    {
        return $this->belongsTo(Personal::class, 'coordinador_id');
    }
}
