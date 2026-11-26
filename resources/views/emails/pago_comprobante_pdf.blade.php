<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Comprobante de pago {{ $cotizacion->folio }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 12px; }
        .header { border-bottom: 2px solid #4f46e5; margin-bottom: 22px; padding-bottom: 14px; text-align: center; }
        .header h1 { color: #4f46e5; font-size: 22px; margin: 0 0 5px; }
        .header p { color: #6b7280; margin: 4px 0; }
        h2 { border-bottom: 1px solid #d1d5db; color: #4f46e5; font-size: 14px; padding-bottom: 6px; }
        table { border-collapse: collapse; margin: 8px 0 20px; width: 100%; }
        td { border-bottom: 1px solid #e5e7eb; padding: 8px; }
        td:first-child { color: #4b5563; width: 35%; }
        .amount { color: #047857; font-size: 16px; font-weight: bold; }
        .footer { border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 10px; margin-top: 28px; padding-top: 12px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>SIGESGA</h1>
        <p>Comprobante de pago con tarjeta</p>
        <p>Cotización <strong>{{ $cotizacion->folio ?: 'COT-' . str_pad((string) $cotizacion->id, 5, '0', STR_PAD_LEFT) }}</strong></p>
    </div>

    <h2>Datos del cliente y concepto</h2>
    <table>
        <tr><td>Cliente</td><td>{{ $cotizacion->cliente?->nombre ?? '—' }}</td></tr>
        <tr><td>Correo</td><td>{{ $cotizacion->cliente?->email ?: $cotizacion->cliente?->usuario?->email ?: '—' }}</td></tr>
        <tr><td>Concepto</td><td>Pago de cotización {{ $cotizacion->folio ?: 'COT-' . str_pad((string) $cotizacion->id, 5, '0', STR_PAD_LEFT) }}</td></tr>
        <tr><td>Servicio</td><td>{{ $cotizacion->tipoServicio?->nombre ?? '—' }}</td></tr>
    </table>

    <h2>Detalle del pago</h2>
    <table>
        <tr><td>Estado</td><td>Pagado</td></tr>
        <tr><td>Monto</td><td class="amount">${{ number_format((float) $pago->monto, 2) }} {{ $pago->moneda ?? 'MXN' }}</td></tr>
        <tr><td>Fecha y hora</td><td>{{ $pago->pagado_en?->format('d/m/Y H:i:s') ?? '—' }}</td></tr>
        <tr><td>Referencia SIGESGA</td><td>{{ $pago->referencia }}</td></tr>
        <tr><td>Clave de rastreo Mercado Pago</td><td>{{ $pago->pasarela_transaccion_id ?: '—' }}</td></tr>
        <tr><td>Tarjeta</td><td>{{ $pago->tarjeta_marca }} terminación {{ $pago->tarjeta_ultimos4 }}</td></tr>
        <tr><td>Titular</td><td>{{ $pago->tarjeta_titular }}</td></tr>
        <tr><td>Pasarela</td><td>{{ $pago->pasarela === 'mercadopago_orders' ? 'Mercado Pago' : $pago->pasarela }}</td></tr>
    </table>

    <div class="footer">Comprobante generado automáticamente por SIGESGA. No contiene el número completo de la tarjeta ni el CVV.</div>
</body>
</html>
