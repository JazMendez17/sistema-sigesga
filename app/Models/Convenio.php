<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Modelo de Convenio
class Convenio extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'aseguradora_id',
        'nombre_convenio_poliza',
        'codigo_convenio',
        'fecha_inicio',
        'fecha_fin',
        'renovacion_automatica',
        'exclusivo',
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
        'periodicidad_corte',
        'requiere_folio_cfdi',
        'iva_incluido',
        'tope_credito',
        'aviso_previo_terminacion_dias',
        'proceso_envio_facturas',
        'estatus',
    ];

    // RelaciÃ³n con empresa
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    // RelaciÃ³n con aseguradora
    public function aseguradora(): BelongsTo
    {
        return $this->belongsTo(Aseguradora::class);
    }

    // RelaciÃ³n con tipo de servicio
    public function tipoServicio(): BelongsTo
    {
        return $this->belongsTo(CatalogoServicio::class, 'tipo_servicio_id');
    }

    // RelaciÃ³n con coberturas del convenio
    public function convenioCoberturas(): HasMany
    {
        return $this->hasMany(ConvenioCobertura::class);
    }

    // RelaciÃ³n con unidades autorizadas del convenio
    public function convenioUnidadesAutorizadas(): HasMany
    {
        return $this->hasMany(ConvenioUnidadesAutorizada::class);
    }

    // RelaciÃ³n con maniobras especiales del convenio
    public function convenioManiobrasEspeciales(): HasMany
    {
        return $this->hasMany(ConvenioManiobrasEspeciale::class);
    }

    // RelaciÃ³n con documentos requeridos del convenio
    public function convenioDocumentosRequeridos(): HasMany
    {
        return $this->hasMany(ConvenioDocumentosRequerido::class);
    }

    // RelaciÃ³n con tarifas del convenio
    public function convenioTarifas(): HasMany
    {
        return $this->hasMany(ConvenioTarifa::class);
    }

    // RelaciÃ³n con cotizaciones
    public function cotizaciones(): HasMany
    {
        return $this->hasMany(Cotizacione::class, 'convenio_aplicado_id');
    }
}
