<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Modelo de Color de Módulo de Empresa
class EmpresaModuloColore extends Model
{
    use HasFactory;

    protected $table = 'empresa_modulo_colores';

    protected $fillable = [
        'empresa_id',
        'modulo',
        'color',
    ];

    // Relación con empresa
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }
}
