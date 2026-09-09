<?php

namespace App\Models;

use App\Models\Concerns\Ocultable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// Modelo de Servicio de Empresa
class EmpresaServicio extends Model
{
        use Ocultable;
use HasFactory;

    public $timestamps = false;

    protected $table = 'empresa_servicios';

    protected $fillable = [
        'empresa_id',
        'tipo',
        'descripcion',
        'foto',
        'orden',
        'activo',
        'color',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    // Relación con empresa
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
