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

    public $timestamps = true;

    public const UPDATED_AT = null;

    protected $fillable = [
        'servicio_id',
        'cliente_id',
        'estrellas',
        'comentario',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'estrellas' => 'integer',
            'created_at' => 'datetime',
        ];
    }

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
