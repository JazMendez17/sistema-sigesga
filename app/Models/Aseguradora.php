<?php

namespace App\Models;

use App\Models\Concerns\Ocultable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Modelo de Aseguradora
class Aseguradora extends Model
{
        use Ocultable;
use HasFactory;

    protected $fillable = [
        'empresa_id',
        'nombre',
        'nombre_comercial',
        'rfc',
        'telefono',
    ];

    // RelaciÃ³n con empresa
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    // RelaciÃ³n con contactos de aseguradora
    public function aseguradoraContactos(): HasMany
    {
        return $this->hasMany(AseguradoraContacto::class);
    }

    // RelaciÃ³n con clientes
    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class);
    }

    // RelaciÃ³n con convenios
    public function convenios(): HasMany
    {
        return $this->hasMany(Convenio::class);
    }
}
