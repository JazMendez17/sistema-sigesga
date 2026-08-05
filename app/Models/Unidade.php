<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

// Modelo de Unidad
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

    protected $appends = ['nombre'];

    public function getNombreAttribute(): string
    {
        return $this->placas . ($this->numero_economico ? ' - ' . $this->numero_economico : '');
    }

    // Relación con empresa
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    // Relación con oficina
    public function oficina()
    {
        return $this->belongsTo(Oficina::class, 'oficina_id');
    }

    // Relación con operador asignado
    public function operadorAsignado()
    {
        return $this->belongsTo(Operadore::class, 'operador_asignado_id');
    }

    // Relación con mantenimientos
    public function unidadMantenimientos()
    {
        return $this->hasMany(UnidadMantenimiento::class, 'unidad_id');
    }

    // Relación con servicios
    public function servicios()
    {
        return $this->hasMany(Servicio::class, 'unidad_id');
    }
}
