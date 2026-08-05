<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

// Modelo de Operador
class Operadore extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'operadores';

    protected $fillable = [
        'empresa_id',
        'empleado_id',
        'tipo_licencia',
        'numero_licencia',
        'fecha_expedicion',
        'fecha_vigencia',
        'disponible',
    ];

    protected function casts(): array
    {
        return [
            'fecha_expedicion' => 'date',
            'fecha_vigencia' => 'date',
            'disponible' => 'boolean',
        ];
    }

    // Relación con empresa
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    // Relación con empleado
    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    // Relación con servicios
    public function servicios()
    {
        return $this->hasMany(Servicio::class, 'operador_id');
    }

    // Relación con unidad
    public function unidad()
    {
        return $this->hasOne(Unidade::class, 'operador_asignado_id');
    }
}
