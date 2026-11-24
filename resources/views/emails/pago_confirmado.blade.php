<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Confirmación de Pago - SIGESGA</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f3f4f6; padding: 24px; color: #1f2937;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; padding: 28px; box-shadow: 0 4px 16px rgba(0,0,0,0.06);">
        <h2 style="margin-top: 0; color: #059669;">
            {{ $paraEquipo ? 'Pago con Tarjeta Recibido' : '¡Tu pago con tarjeta fue confirmado!' }}
        </h2>

        <p style="font-size: 14px; line-height: 1.6;">
            @if($paraEquipo)
                Se ha confirmado un pago con tarjeta correspondiente a la cotización <strong>{{ $cotizacion->folio }}</strong>.
            @else
                Hola <strong>{{ $cotizacion->cliente?->nombre ?? 'Cliente' }}</strong>, hemos recibido correctamente tu pago con tarjeta para la cotización <strong>{{ $cotizacion->folio }}</strong>.
            @endif
        </p>

        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 12px; padding: 16px; margin: 20px 0; font-size: 14px;">
            <p style="margin: 6px 0;"><strong>Folio de cotización:</strong> {{ $cotizacion->folio }}</p>
            <p style="margin: 6px 0;"><strong>Monto pagado:</strong> ${{ number_format((float) $pago->monto, 2) }} {{ $pago->moneda ?? 'MXN' }}</p>
            <p style="margin: 6px 0;"><strong>Tarjeta:</strong> {{ $pago->tarjeta_marca }} •••• {{ $pago->tarjeta_ultimos4 }}</p>
            <p style="margin: 6px 0;"><strong>Titular:</strong> {{ $pago->tarjeta_titular }}</p>
            <p style="margin: 6px 0;"><strong>Referencia interna:</strong> {{ $pago->referencia }}</p>
            @if($pago->pasarela_transaccion_id)
                <p style="margin: 6px 0;"><strong>Clave de rastreo:</strong> {{ $pago->pasarela_transaccion_id }}</p>
            @endif
            <p style="margin: 6px 0;"><strong>Fecha y hora:</strong> {{ $pago->pagado_en?->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i') }}</p>
        </div>

        <p style="font-size: 13px; color: #6b7280; margin-bottom: 0;">
            Este es un comprobante automático generado por SIGESGA.
        </p>
    </div>
</body>
</html>
