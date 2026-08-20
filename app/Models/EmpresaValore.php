<?php

namespace App\Models;

use App\Models\Concerns\Ocultable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// Modelo de Valor de Empresa
class EmpresaValore extends Model
{
        use Ocultable;
use HasFactory;

    public $timestamps = false;

    protected $table = 'empresa_valores';

    protected $fillable = [
        'empresa_id',
        'valor',
        'descripcion',
        'orden',
    ];

    // Relación con empresa
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
