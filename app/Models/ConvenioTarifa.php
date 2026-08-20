<?php

namespace App\Models;

use App\Models\Concerns\Ocultable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Modelo de Tarifa de Convenio
class ConvenioTarifa extends Model
{
        use Ocultable;
use HasFactory;

    protected $table = 'convenio_tarifas';

    protected $fillable = [
        'convenio_id',
        'servicio_id',
        'servicio',
        'alcance',
        'banderazo',
        'km_incluidos',
        'costo_km_extra',
        'tarifa_nocturna_recargo_pct',
        'tarifa_domingo_festivo_recargo_pct',
        'minutos_espera_incluidos',
        'costo_espera_adicional_hora',
        'descuento_pct',
        'tipo_descuento',
    ];

    // RelaciÃ³n con convenio
    public function convenio(): BelongsTo
    {
        return $this->belongsTo(Convenio::class);
    }

    // RelaciÃ³n con tipo de servicio
    public function tipoServicio(): BelongsTo
    {
        return $this->belongsTo(CatalogoServicio::class, 'servicio_id');
    }
}
