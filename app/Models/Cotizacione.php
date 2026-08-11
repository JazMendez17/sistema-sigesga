<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

// Modelo de Cotización
class Cotizacione extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cotizaciones';

    protected $fillable = [
        'empresa_id',
        'cliente_id',
        'convenio_aplicado_id',
        'tarifa_empresa_aplicada_id',
        'usuario_creador_id',
        'folio',
        'tipo_servicio_id',
        'origen_direccion',
        'origen_lat',
        'origen_lng',
        'destino_direccion',
        'destino_lat',
        'destino_lng',
        'ruta_polyline',
        'distancia_km',
        'tiempo_estimado_minutos',
        'incluye_peajes',
        'costo_aprox_casetas',
        'cargo_zona_especial',
        'costo_banderazo',
        'costo_km',
        'km_excedente',
        'km_incluidos',
        'subtotal',
        'monto_descuento',
        'descuento_pct',
        'monto_iva',
        'costo_total',
        'estatus',
    ];

    // Relación con empresa
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    // Relación con cliente
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    // Relación con convenio aplicado
    public function convenioAplicado(): BelongsTo
    {
        return $this->belongsTo(Convenio::class, 'convenio_aplicado_id');
    }

    // Relación con tarifa de empresa aplicada
    public function tarifaEmpresaAplicada(): BelongsTo
    {
        return $this->belongsTo(TarifasEmpresa::class, 'tarifa_empresa_aplicada_id');
    }

    // Relación con usuario creador
    public function usuarioCreador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_creador_id');
    }

    // Relación con tipo de servicio
    public function tipoServicio(): BelongsTo
    {
        return $this->belongsTo(CatalogoServicio::class, 'tipo_servicio_id');
    }

    // Relación con servicio
    public function servicio(): HasOne
    {
        return $this->hasOne(Servicio::class, 'cotizacion_id');
    }
}
