<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

// Modelo de Servicio
class Servicio extends Model
{
    use HasFactory, SoftDeletes;

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

    // Relación con empresa
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    // Relación con cotización
    public function cotizacion(): BelongsTo
    {
        return $this->belongsTo(Cotizacione::class, 'cotizacion_id');
    }

    // Relación con operador
    public function operador(): BelongsTo
    {
        return $this->belongsTo(Operadore::class);
    }

    // Relación con unidad
    public function unidad(): BelongsTo
    {
        return $this->belongsTo(Unidade::class);
    }

    // Relación con oficina
    public function oficina(): BelongsTo
    {
        return $this->belongsTo(Oficina::class);
    }

    // Relación con bitácora de tiempos
    public function bitacoraTiemposServicio(): HasOne
    {
        return $this->hasOne(BitacoraTiemposServicio::class);
    }

    // Relación con autorizaciones de cancelación
    public function autorizacionesCancelacion(): HasOne
    {
        return $this->hasOne(AutorizacionesCancelacione::class);
    }

    // Relación con calificaciones
    public function calificacionesServicio(): HasOne
    {
        return $this->hasOne(CalificacionesServicio::class);
    }

    // Relación con factura
    public function factura(): HasOne
    {
        return $this->hasOne(Factura::class);
    }
}
