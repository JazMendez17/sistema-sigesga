<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    public function oficinas()
    {
        return $this->hasMany(Oficina::class, 'direccion_id');
    }

    public function empleados()
    {
        return $this->hasMany(Empleado::class, 'direccion_id');
    }

    public function clientes()
    {
        return $this->hasMany(Cliente::class, 'direccion_id');
    }
}
