<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// Modelo de Nosotros de Empresa
class EmpresaNosotros extends Model
{
    use HasFactory;

    protected $table = 'empresa_nosotros';

    protected $fillable = [
        'empresa_id',
        'quienes_somos',
        'mision',
        'vision',
        'prioridad',
    ];

    // Relación con empresa
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
