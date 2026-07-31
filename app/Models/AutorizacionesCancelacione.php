<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutorizacionesCancelacione extends Model
{
    use HasFactory;

    protected $table = 'autorizaciones_cancelacion';

    public $timestamps = false;

    protected $fillable = [
        'servicio_id',
        'usuario_solicitante_id',
        'usuario_resolutor_id',
        'motivo_cancelacion',
        'tipo_incidencia',
        'estatus',
        'fecha_solicitud',
        'fecha_resolucion',
    ];

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class);
    }

    public function usuarioSolicitante(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_solicitante_id');
    }

    public function usuarioResolutor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_resolutor_id');
    }
}
