<?php

namespace App\Models;

use App\Models\Concerns\Ocultable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Modelo de SLA / Penalizaciones por Convenio
class ConvenioSla extends Model
{
        use Ocultable;
public $timestamps = false;

    protected $table = 'convenio_sla';

    protected $fillable = [
        'convenio_id',
        'tiempo_max_respuesta_urbano_min',
        'tiempo_max_respuesta_carretera_min',
        'disponibilidad',
        'penalizacion_incumplimiento',
        'protocolo_asignacion',
    ];

    public function convenio(): BelongsTo
    {
        return $this->belongsTo(Convenio::class);
    }
}
