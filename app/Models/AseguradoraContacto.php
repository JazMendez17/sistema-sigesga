<?php

namespace App\Models;

use App\Models\Concerns\Ocultable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Modelo de Contacto de Aseguradora
class AseguradoraContacto extends Model
{
        use Ocultable;
use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'aseguradora_id',
        'departamento',
        'nombre_contacto',
        'telefono',
        'email',
        'red_social',
        'activo',
    ];

    // RelaciÃ³n con aseguradora
    public function aseguradora(): BelongsTo
    {
        return $this->belongsTo(Aseguradora::class);
    }
}
