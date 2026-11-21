<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagoCotizacion extends Model
{
    use HasFactory;

    protected $table = 'pagos_cotizacion';

    protected $fillable = [
        'empresa_id',
        'cotizacion_id',
        'cliente_id',
        'usuario_id',
        'referencia',
        'metodo',
        'pasarela',
        'pasarela_transaccion_id',
        'monto',
        'moneda',
        'tarjeta_titular',
        'tarjeta_marca',
        'tarjeta_ultimos4',
        'tarjeta_exp_mes',
        'tarjeta_exp_anio',
        'estatus',
        'pagado_en',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'tarjeta_exp_mes' => 'integer',
            'tarjeta_exp_anio' => 'integer',
            'pagado_en' => 'datetime',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function cotizacion(): BelongsTo
    {
        return $this->belongsTo(Cotizacione::class, 'cotizacion_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }
}
