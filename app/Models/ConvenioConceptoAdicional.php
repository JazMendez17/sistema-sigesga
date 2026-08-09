<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Modelo de Conceptos Adicionales por Convenio (casetas, estancia, resguardo)
class ConvenioConceptoAdicional extends Model
{
    public $timestamps = false;

    protected $table = 'convenio_conceptos_adicionales';

    protected $fillable = [
        'convenio_id',
        'cubre_casetas',
        'forma_pago_casetas',
        'costo_estadia_dia',
        'dias_gracia_estadia',
        'costo_resguardo_nocturno',
        'genera_cargo_cliente_final',
    ];

    protected function casts(): array
    {
        return [
            'cubre_casetas' => 'boolean',
            'genera_cargo_cliente_final' => 'boolean',
        ];
    }

    public function convenio(): BelongsTo
    {
        return $this->belongsTo(Convenio::class);
    }
}
