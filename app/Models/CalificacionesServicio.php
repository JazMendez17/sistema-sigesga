<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Modelo de Calificaciones de Servicio
class CalificacionesServicio extends Model
{
    use HasFactory;

    protected $table = 'calificaciones_servicio';

    public $timestamps = false;

    protected $fillable = [
        'servicio_id',
        'cliente_id',
        'estrellas',
        'comentario',
    ];

    // Relación con servicio
    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class);
    }

    // Relación con cliente
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
}
