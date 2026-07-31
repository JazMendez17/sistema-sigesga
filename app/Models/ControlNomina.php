<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ControlNomina extends Model
{
    protected $table = 'control_nomina';

    protected $fillable = [
        'empresa_id',
        'operador_id',
        'fecha_desde',
        'fecha_hasta',
        'sueldo_base_semanal',
        'bonos_servicios',
        'descuentos_prestamos',
        'estatus',
    ];

    protected function casts(): array
    {
        return [
            'fecha_desde' => 'date',
            'fecha_hasta' => 'date',
            'sueldo_base_semanal' => 'decimal:2',
            'bonos_servicios' => 'decimal:2',
            'descuentos_prestamos' => 'decimal:2',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function operador(): BelongsTo
    {
        return $this->belongsTo(Operadore::class);
    }
}
