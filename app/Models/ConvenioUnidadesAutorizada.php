<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Modelo de Unidad Autorizada de Convenio
class ConvenioUnidadesAutorizada extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'convenio_id',
        'tipo_grua',
        'peso_maximo_kg',
        'equipamiento',
    ];

    // Relación con convenio
    public function convenio(): BelongsTo
    {
        return $this->belongsTo(Convenio::class);
    }
}
