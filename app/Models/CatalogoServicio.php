<?php

namespace App\Models;

use App\Models\Concerns\Ocultable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// Modelo de CatÃ¡logo de Servicios
class CatalogoServicio extends Model
{
        use Ocultable;
use HasFactory;

    protected $table = 'catalogo_servicios';

    protected $fillable = [
        'empresa_id',
        'nombre',
        'requiere_maniobra',
        'activo',
    ];

    // RelaciÃ³n con empresa
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    // RelaciÃ³n con convenios
    public function convenios()
    {
        return $this->hasMany(Convenio::class, 'tipo_servicio_id');
    }

    // RelaciÃ³n con tarifas de empresa
    public function tarifasEmpresa()
    {
        return $this->hasMany(TarifasEmpresa::class, 'tipo_servicio_id');
    }

    // RelaciÃ³n con cotizaciones
    public function cotizaciones()
    {
        return $this->hasMany(Cotizacione::class, 'tipo_servicio_id');
    }
}
