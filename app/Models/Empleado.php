<?php

namespace App\Models;

use App\Models\Concerns\Ocultable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// Modelo de Empleado
class Empleado extends Model
{
        use Ocultable;
use HasFactory;

    protected $table = 'empleados';

    protected $fillable = [
        'empresa_id',
        'oficina_id',
        'direccion_id',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'sexo',
        'curp',
        'fecha_nacimiento',
        'telefono',
        'telefono_local',
        'correo',
        'folio_ine',
        'nacionalidad',
        'puesto',
        'sueldo_diario',
    ];

    // RelaciÃ³n con empresa
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    // RelaciÃ³n con oficina
    public function oficina()
    {
        return $this->belongsTo(Oficina::class, 'oficina_id');
    }

    // RelaciÃ³n con direcciÃ³n
    public function direccion()
    {
        return $this->belongsTo(Direccion::class, 'direccion_id');
    }

    // RelaciÃ³n con usuario
    public function usuario()
    {
        return $this->hasOne(Usuario::class, 'empleado_id');
    }

    // RelaciÃ³n con operador
    public function operador()
    {
        return $this->hasOne(Operadore::class, 'empleado_id');
    }
}
