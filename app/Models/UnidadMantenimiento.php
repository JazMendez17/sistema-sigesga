<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UnidadMantenimiento extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'unidad_mantenimientos';

    protected $fillable = [
        'empresa_id',
        'unidad_id',
        'tipo',
        'fecha',
        'kilometraje',
        'costo',
        'proximo_mantenimiento_fecha',
        'proximo_mantenimiento_km',
        'observaciones',
        'created_at',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (!$model->created_at) {
                $model->created_at = now();
            }
        });
    }

    public function unidad()
    {
        return $this->belongsTo(Unidade::class, 'unidad_id');
    }
}
