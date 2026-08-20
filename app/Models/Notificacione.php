<?php

namespace App\Models;

use App\Models\Concerns\Ocultable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Modelo de NotificaciÃ³n
class Notificacione extends Model
{
        use Ocultable;
use HasFactory;

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

    // RelaciÃ³n con empresa
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    // RelaciÃ³n con usuario
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }
}
