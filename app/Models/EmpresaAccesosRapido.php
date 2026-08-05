<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// Modelo de Acceso Rápido de Empresa
class EmpresaAccesosRapido extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'empresa_accesos_rapidos';

    protected $fillable = [
        'empresa_id',
        'titulo',
        'link',
        'icono',
        'orden',
    ];

    // Relación con empresa
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
