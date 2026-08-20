<?php

namespace App\Models;

use App\Models\Concerns\Ocultable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// Modelo de Oficina
class Oficina extends Model
{
        use Ocultable;
use HasFactory;

    protected $table = 'oficinas';

    protected $fillable = [
        'empresa_id',
        'direccion_id',
        'nombre',
        'telefono',
        'email',
        'encargado',
    ];

    // RelaciÃ³n con empresa
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    // RelaciÃ³n con direcciÃ³n
    public function direccion()
    {
        return $this->belongsTo(Direccion::class, 'direccion_id');
    }

    // RelaciÃ³n con empleados
    public function empleados()
    {
        return $this->hasMany(Empleado::class, 'oficina_id');
    }

    // RelaciÃ³n con unidades
    public function unidades()
    {
        return $this->hasMany(Unidade::class, 'oficina_id');
    }

    // RelaciÃ³n con servicios
    public function servicios()
    {
        return $this->hasMany(Servicio::class, 'oficina_id');
    }
}
