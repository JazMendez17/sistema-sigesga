<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TarifasEmpresa extends Model
{
    use HasFactory, SoftDeletes;

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

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function tipoServicio(): BelongsTo
    {
        return $this->belongsTo(CatalogoServicio::class, 'tipo_servicio_id');
    }
}
