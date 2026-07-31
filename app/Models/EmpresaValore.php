<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmpresaValore extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'empresa_valores';

    protected $fillable = [
        'empresa_id',
        'valor',
        'descripcion',
        'orden',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
