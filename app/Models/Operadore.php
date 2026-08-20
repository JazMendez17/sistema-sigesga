<?php

namespace App\Models;

use App\Models\Concerns\Ocultable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// Modelo de Operador
class Operadore extends Model
{
        use Ocultable;
use HasFactory;

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

    // RelaciÃ³n con empresa
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    // RelaciÃ³n con empleado
    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    // RelaciÃ³n con servicios
    public function servicios()
    {
        return $this->hasMany(Servicio::class, 'operador_id');
    }

    // RelaciÃ³n con unidad
    public function unidad()
    {
        return $this->hasOne(Unidade::class, 'operador_asignado_id');
    }
}
