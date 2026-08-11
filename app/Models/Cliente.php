<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Modelo de Cliente
class Cliente extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'empresa_id',
        'usuario_id',
        'aseguradora_id',
        'tipo_cliente',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'sexo',
        'curp',
        'fecha_nacimiento',
        'direccion_id',
        'telefono',
        'telefono_local',
        'email',
        'folio_ine',
        'nacionalidad',
        'contacto_enlace',
        'numero_poliza',
        'tipo_cobertura_poliza',
    ];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
        ];
    }

    // Relación con empresa
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    // Relación con usuario
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    // Relación con aseguradora
    public function aseguradora(): BelongsTo
    {
        return $this->belongsTo(Aseguradora::class);
    }

    // Relación con dirección
    public function direccion(): BelongsTo
    {
        return $this->belongsTo(Direccion::class);
    }

    // Relación con cotizaciones
    public function cotizaciones(): HasMany
    {
        return $this->hasMany(Cotizacione::class);
    }

    // Relación con facturas
    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class);
    }

    // Relación con calificaciones de servicio
    public function calificacionesServicio(): HasMany
    {
        return $this->hasMany(CalificacionesServicio::class);
    }
}
