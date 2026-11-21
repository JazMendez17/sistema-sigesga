<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Modelo de Tarifa de Empresa
class TarifasEmpresa extends Model
{
        use HasFactory;

    protected $table = 'tarifas_empresa';

    protected $fillable = [
        'empresa_id',
        'tipo_servicio_id',
        'nombre_tarifa',
        'servicio',
        'alcance',
        'tipo_ruta',
        'costo_banderazo',
        'costo_km',
        'costo_km_extra',
        'km_incluidos',
        'tarifa_nocturna_recargo_pct',
        'tarifa_domingo_festivo_recargo_pct',
        'minutos_espera_incluidos',
        'costo_espera_adicional_hora',
        'descuento_pct',
        'tipo_descuento',
        'cubre_casetas_peaje',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'cubre_casetas_peaje' => 'boolean',
            'activo' => 'boolean',
        ];
    }

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
