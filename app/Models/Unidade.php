<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unidade extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'unidades';

    protected $fillable = [
        'empresa_id',
        'oficina_id',
        'operador_asignado_id',
        'marca',
        'tipo',
        'modelo',
        'placas',
        'numero_economico',
        'seguro_vencimiento',
        'estado_emplacado',
        'activo',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function oficina()
    {
        return $this->belongsTo(Oficina::class, 'oficina_id');
    }

    public function operadorAsignado()
    {
        return $this->belongsTo(Operadore::class, 'operador_asignado_id');
    }

    public function unidadMantenimientos()
    {
        return $this->hasMany(UnidadMantenimiento::class, 'unidad_id');
    }

    public function servicios()
    {
        return $this->hasMany(Servicio::class, 'unidad_id');
    }
}
