<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Modelo de Ubicación de Unidad
class UnidadesUbicacione extends Model
{
    public $timestamps = false;

    protected $table = 'unidades_ubicaciones';

    protected $fillable = [
        'unidad_id',
        'servicio_id',
        'lat',
        'lng',
        'registrado_en',
    ];

    protected function casts(): array
    {
        return [
            'registrado_en' => 'datetime',
            'lat' => 'decimal:8',
            'lng' => 'decimal:8',
        ];
    }

    // Relación con unidad
    public function unidad(): BelongsTo
    {
        return $this->belongsTo(Unidade::class);
    }

    // Relación con servicio
    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class);
    }
}
