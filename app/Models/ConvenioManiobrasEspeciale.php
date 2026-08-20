<?php

namespace App\Models;

use App\Models\Concerns\Ocultable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Modelo de Maniobra Especial de Convenio
class ConvenioManiobrasEspeciale extends Model
{
        use Ocultable;
use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'convenio_id',
        'concepto',
        'aplica',
        'forma_cobro',
        'costo',
    ];

    // Relación con convenio
    public function convenio(): BelongsTo
    {
        return $this->belongsTo(Convenio::class);
    }
}
