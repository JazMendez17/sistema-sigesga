<?php

// Modelo de AuditoriaEliminacione

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditoriaEliminacione extends Model
{
    protected $table = 'auditoria_eliminaciones';

    public $timestamps = false;

    protected $fillable = [
        'modelo',
        'registro_id',
        'datos',
        'usuario_id',
        'created_at',
    ];

    protected $casts = [
        'datos' => 'array',
        'created_at' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}