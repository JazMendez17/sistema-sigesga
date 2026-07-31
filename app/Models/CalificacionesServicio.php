<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
}
