<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// Modelo de Servicio de Empresa
class EmpresaServicio extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'empresa_servicios';

    protected $fillable = [
        'empresa_id',
        'tipo',
        'descripcion',
        'foto',
        'orden',
    ];

    // Relación con empresa
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
