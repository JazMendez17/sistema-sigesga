<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Modelo de Aseguradora
class Aseguradora extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'empresa_id',
        'nombre',
        'nombre_comercial',
        'rfc',
        'telefono',
    ];

    // Relación con empresa
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    // Relación con contactos de aseguradora
    public function aseguradoraContactos(): HasMany
    {
        return $this->hasMany(AseguradoraContacto::class);
    }

    // Relación con clientes
    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class);
    }

    // Relación con convenios
    public function convenios(): HasMany
    {
        return $this->hasMany(Convenio::class);
    }
}
