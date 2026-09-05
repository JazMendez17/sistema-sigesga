<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><title>Nueva cotización</title></head>
<body>
    <h1>Nueva cotización {{ $cotizacion->folio }}</h1>
    <p>Hola {{ $cotizacion->cliente?->nombre ?? 'cliente' }},</p>
    <p>Tu cotización está disponible en el portal para revisarla y aprobarla.</p>
    <p><strong>Servicio:</strong> {{ $cotizacion->tipoServicio?->nombre ?? '—' }}</p>
    <h2>Desglose</h2>
    <ul>
        <li>Distancia: {{ $distancia }} km</li>
        <li>Banderazo: ${{ $banderazo }}</li>
        <li>KM incluidos: {{ $km_incluidos }}</li>
        <li>KM excedentes: {{ $km_excedente }} a ${{ $costo_km }}/km</li>
        <li>Subtotal: ${{ $subtotal }}</li>
        <li>Descuento ({{ $descuento_pct }}%): -${{ $monto_descuento }}</li>
        <li>IVA: ${{ $iva }}</li>
        <li>Casetas: ${{ $casetas }} {{ $incluye_peajes ? '(incluidas)' : '(cargo adicional)' }}</li>
    </ul>
    <p><strong>Total estimado:</strong> ${{ $total }}</p>
    <p><a href="{{ $urlAprobacion }}">Ver y aprobar cotización</a></p>
</body>
</html>
