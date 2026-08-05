<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Modelo de Notificación
class Notificacione extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'notificaciones';

    protected $fillable = [
        'empresa_id',
        'usuario_id',
        'mensaje',
        'canal',
        'estado',
        'proveedor_mensaje_id',
        'intentos_envio',
        'error_detalle',
    ];

    // Relación con empresa
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    // Relación con usuario
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }
}
