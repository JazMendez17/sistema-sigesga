<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// Modelo de Dirección
class Direccion extends Model
{
    use HasFactory;

    protected $table = 'direcciones';

    protected $fillable = [
        'calle',
        'numero_exterior',
        'numero_interior',
        'colonia',
        'codigo_postal',
        'municipio_alcaldia',
        'ciudad',
        'estado',
        'pais',
        'referencias',
    ];

    // Relación con oficinas
    public function oficinas()
    {
        return $this->hasMany(Oficina::class, 'direccion_id');
    }

    // Relación con empleados
    public function empleados()
    {
        return $this->hasMany(Empleado::class, 'direccion_id');
    }

    // Relación con clientes
    public function clientes()
    {
        return $this->hasMany(Cliente::class, 'direccion_id');
    }
}
