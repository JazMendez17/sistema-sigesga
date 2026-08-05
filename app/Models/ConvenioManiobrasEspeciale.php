<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Modelo de Maniobra Especial de Convenio
class ConvenioManiobrasEspeciale extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'convenio_id',
        'concepto',
        'costo',
    ];

    // Relación con convenio
    public function convenio(): BelongsTo
    {
        return $this->belongsTo(Convenio::class);
    }
}
