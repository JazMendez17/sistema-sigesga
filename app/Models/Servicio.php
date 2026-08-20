<?php

namespace App\Models;

use App\Models\Concerns\Ocultable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

// Modelo de Servicio
class Servicio extends Model
{
        use Ocultable;
use HasFactory;

    protected $fillable = [
        'empresa_id',
        'cotizacion_id',
        'operador_id',
        'unidad_id',
        'oficina_id',
        'estado',
        'kms_salida',
        'kms_llegada_cliente',
        'kms_termino_servicio',
        'kms_regreso_base',
        'kms_cobrados_reales',
        'cargo_zona_especial',
        'costo_final_real',
        'observaciones',
    ];

    // RelaciÃ³n con empresa
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    // RelaciÃ³n con cotizaciÃ³n
    public function cotizacion(): BelongsTo
    {
        return $this->belongsTo(Cotizacione::class, 'cotizacion_id');
    }

    // RelaciÃ³n con operador
    public function operador(): BelongsTo
    {
        return $this->belongsTo(Operadore::class);
    }

    // RelaciÃ³n con unidad
    public function unidad(): BelongsTo
    {
        return $this->belongsTo(Unidade::class);
    }

    // RelaciÃ³n con oficina
    public function oficina(): BelongsTo
    {
        return $this->belongsTo(Oficina::class);
    }

    // RelaciÃ³n con bitÃ¡cora de tiempos
    public function bitacoraTiemposServicio(): HasOne
    {
        return $this->hasOne(BitacoraTiemposServicio::class);
    }

    // RelaciÃ³n con autorizaciones de cancelaciÃ³n
    public function autorizacionesCancelacion(): HasOne
    {
        return $this->hasOne(AutorizacionesCancelacione::class);
    }

    // RelaciÃ³n con calificaciones
    public function calificacionesServicio(): HasOne
    {
        return $this->hasOne(CalificacionesServicio::class);
    }

    // RelaciÃ³n con factura
    public function factura(): HasOne
    {
        return $this->hasOne(Factura::class);
    }
}
