<?php

namespace App\Models;

use App\Models\Concerns\Ocultable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Modelo de Cliente
class Cliente extends Model
{
        use Ocultable;
use HasFactory;

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

    // RelaciÃ³n con empresa
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    // RelaciÃ³n con usuario
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    // RelaciÃ³n con aseguradora
    public function aseguradora(): BelongsTo
    {
        return $this->belongsTo(Aseguradora::class);
    }

    // RelaciÃ³n con direcciÃ³n
    public function direccion(): BelongsTo
    {
        return $this->belongsTo(Direccion::class);
    }

    // RelaciÃ³n con cotizaciones
    public function cotizaciones(): HasMany
    {
        return $this->hasMany(Cotizacione::class);
    }

    // RelaciÃ³n con facturas
    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class);
    }

    // RelaciÃ³n con calificaciones de servicio
    public function calificacionesServicio(): HasMany
    {
        return $this->hasMany(CalificacionesServicio::class);
    }
}
