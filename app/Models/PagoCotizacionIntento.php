<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagoCotizacionIntento extends Model
{
    protected $table = 'pagos_cotizacion_intentos';

    protected $fillable = [
        'empresa_id',
        'cotizacion_id',
        'pago_cotizacion_id',
        'idempotency_key',
        'provider_idempotency_key',
        'referencia',
        'estado',
        'mercadopago_preference_id',
        'checkout_url',
        'mercadopago_order_id',
        'mercadopago_payment_id',
        'request_payload',
        'mensaje_error',
        'rechazo_notificado_en',
    ];

    protected function casts(): array
    {
        return [
            'request_payload' => 'encrypted:array',
            'rechazo_notificado_en' => 'datetime',
            'ultimo_evento_en' => 'datetime',
        ];
    }

    public function cotizacion(): BelongsTo
    {
        return $this->belongsTo(Cotizacione::class, 'cotizacion_id');
    }

    public function pago(): BelongsTo
    {
        return $this->belongsTo(PagoCotizacion::class, 'pago_cotizacion_id');
    }
}
