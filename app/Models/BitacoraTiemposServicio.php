<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BitacoraTiemposServicio extends Model
{
    use HasFactory;

    protected $table = 'bitacora_tiempos_servicio';

    public $timestamps = false;

    protected $fillable = [
        'servicio_id',
        'hora_asignado',
        'hora_inicio_servicio',
        'hora_en_sitio_origen',
        'hora_salida_destino',
        'hora_en_destino',
        'hora_finalizado',
    ];

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class);
    }
}
