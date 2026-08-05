<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

// Modelo de Empleado
class Empleado extends Model
{
    use HasFactory, SoftDeletes;

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

    // Relación con dirección
    public function direccion()
    {
        return $this->belongsTo(Direccion::class, 'direccion_id');
    }

    // Relación con usuario
    public function usuario()
    {
        return $this->hasOne(Usuario::class, 'empleado_id');
    }

    // Relación con operador
    public function operador()
    {
        return $this->hasOne(Operadore::class, 'empleado_id');
    }
}
