<?php

namespace App\Models;

use App\Models\Concerns\Ocultable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Modelo de Tarifa de Empresa
class TarifasEmpresa extends Model
{
        use Ocultable;
use HasFactory;

    protected $table = 'tarifas_empresa';

    protected $fillable = [
        'empresa_id',
        'tipo_servicio_id',
        'nombre_tarifa',
        'tipo_ruta',
        'costo_banderazo',
        'costo_km',
        'km_incluidos',
        'cubre_casetas_peaje',
        'activo',
    ];

    // RelaciÃ³n con empresa
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    // RelaciÃ³n con tipo de servicio
    public function tipoServicio(): BelongsTo
    {
        return $this->belongsTo(CatalogoServicio::class, 'tipo_servicio_id');
    }
}
