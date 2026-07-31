<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class CatalogoServicio extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'catalogo_servicios';

    protected $fillable = [
        'empresa_id',
        'nombre',
        'requiere_maniobra',
        'activo',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function convenios()
    {
        return $this->hasMany(Convenio::class, 'tipo_servicio_id');
    }

    public function tarifasEmpresa()
    {
        return $this->hasMany(TarifasEmpresa::class, 'tipo_servicio_id');
    }

    public function cotizaciones()
    {
        return $this->hasMany(Cotizacione::class, 'tipo_servicio_id');
    }
}
