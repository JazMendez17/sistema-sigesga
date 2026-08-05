<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Modelo de Cobertura de Convenio
class ConvenioCobertura extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'convenio_id',
        'tipo_cobertura',
    ];

    // Relación con convenio
    public function convenio(): BelongsTo
    {
        return $this->belongsTo(Convenio::class);
    }
}
