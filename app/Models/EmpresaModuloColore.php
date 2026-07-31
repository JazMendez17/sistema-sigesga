<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmpresaModuloColore extends Model
{
    use HasFactory;

    protected $table = 'empresa_modulo_colores';

    protected $fillable = [
        'empresa_id',
        'modulo',
        'color',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }
}
