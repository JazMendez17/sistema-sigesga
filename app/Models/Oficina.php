<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

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

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function direccion()
    {
        return $this->belongsTo(Direccion::class, 'direccion_id');
    }

    public function empleados()
    {
        return $this->hasMany(Empleado::class, 'oficina_id');
    }

    public function unidades()
    {
        return $this->hasMany(Unidade::class, 'oficina_id');
    }

    public function servicios()
    {
        return $this->hasMany(Servicio::class, 'oficina_id');
    }
}
