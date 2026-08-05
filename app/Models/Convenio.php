<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Modelo de Convenio
class Convenio extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'empresa_id',
        'aseguradora_id',
        'nombre_convenio_poliza',
        'tipo_servicio_id',
        'tipo_ruta',
        'tipo_cobertura',
        'alcance_geografico',
        'costo_banderazo',
        'costo_km',
        'km_seguros_incluidos',
        'km_maximo_amparado',
        'tope_presupuesto',
        'cubre_casetas_peaje',
        'dias_credito',
        'proceso_envio_facturas',
        'estatus',
    ];

    // Relación con empresa
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    // Relación con aseguradora
    public function aseguradora(): BelongsTo
    {
        return $this->belongsTo(Aseguradora::class);
    }

    // Relación con tipo de servicio
    public function tipoServicio(): BelongsTo
    {
        return $this->belongsTo(CatalogoServicio::class, 'tipo_servicio_id');
    }

    // Relación con coberturas del convenio
    public function convenioCoberturas(): HasMany
    {
        return $this->hasMany(ConvenioCobertura::class);
    }

    // Relación con unidades autorizadas del convenio
    public function convenioUnidadesAutorizadas(): HasMany
    {
        return $this->hasMany(ConvenioUnidadesAutorizada::class);
    }

    // Relación con maniobras especiales del convenio
    public function convenioManiobrasEspeciales(): HasMany
    {
        return $this->hasMany(ConvenioManiobrasEspeciale::class);
    }

    // Relación con documentos requeridos del convenio
    public function convenioDocumentosRequeridos(): HasMany
    {
        return $this->hasMany(ConvenioDocumentosRequerido::class);
    }

    // Relación con cotizaciones
    public function cotizaciones(): HasMany
    {
        return $this->hasMany(Cotizacione::class, 'convenio_aplicado_id');
    }
}
