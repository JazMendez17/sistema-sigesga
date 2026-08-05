<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

// Modelo de Oficina
class Oficina extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'oficinas';

    protected $fillable = [
        'empresa_id',
        'direccion_id',
        'nombre',
        'telefono',
        'email',
        'encargado',
    ];

    // Relación con empresa
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    // Relación con dirección
    public function direccion()
    {
        return $this->belongsTo(Direccion::class, 'direccion_id');
    }

    // Relación con empleados
    public function empleados()
    {
        return $this->hasMany(Empleado::class, 'oficina_id');
    }

    // Relación con unidades
    public function unidades()
    {
        return $this->hasMany(Unidade::class, 'oficina_id');
    }

    // Relación con servicios
    public function servicios()
    {
        return $this->hasMany(Servicio::class, 'oficina_id');
    }
}
