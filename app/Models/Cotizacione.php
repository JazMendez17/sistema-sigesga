<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'costo_total',
        'estatus',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function convenioAplicado(): BelongsTo
    {
        return $this->belongsTo(Convenio::class, 'convenio_aplicado_id');
    }

    public function tarifaEmpresaAplicada(): BelongsTo
    {
        return $this->belongsTo(TarifasEmpresa::class, 'tarifa_empresa_aplicada_id');
    }

    public function usuarioCreador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_creador_id');
    }

    public function tipoServicio(): BelongsTo
    {
        return $this->belongsTo(CatalogoServicio::class, 'tipo_servicio_id');
    }

    public function servicio(): HasOne
    {
        return $this->hasOne(Servicio::class, 'cotizacion_id');
    }
}
