<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><title>Factura {{ $folio }}</title>
<style>
  body { font-family: Arial, sans-serif; margin: 0; padding: 20px; color: #1F2937; }
  .header { text-align: center; border-bottom: 2px solid #4F46E5; padding-bottom: 15px; margin-bottom: 20px; }
  .header h1 { color: #4F46E5; margin: 0; font-size: 24px; }
  .header p { margin: 5px 0; color: #6B7280; font-size: 13px; }
  .section { margin-bottom: 15px; }
  .section h3 { color: #4F46E5; border-bottom: 1px solid #E5E7EB; padding-bottom: 5px; font-size: 14px; }
  table { width: 100%; border-collapse: collapse; margin: 10px 0; }
  th { background: #F3F4F6; padding: 8px; text-align: left; font-size: 12px; color: #6B7280; }
  td { padding: 8px; font-size: 13px; border-bottom: 1px solid #E5E7EB; }
  .total-row td { font-weight: bold; font-size: 16px; border-top: 2px solid #4F46E5; }
  .footer { margin-top: 30px; text-align: center; font-size: 11px; color: #9CA3AF; border-top: 1px solid #E5E7EB; padding-top: 15px; }
</style></head>
<body>
<div class="header">
  <h1>SIGESGA</h1>
  <p>Sistema de Gestión de Grúas y Servicios</p>
  <p><strong>Factura {{ $folio }}</strong> | Fecha: {{ $fecha }}</p>
</div>

<div class="section">
  <h3>Datos del Cliente</h3>
  <table>
    <tr><td width="30%"><strong>Cliente:</strong></td><td>{{ $cliente }}</td></tr>
    <tr><td><strong>Email:</strong></td><td>{{ $email_cliente ?? '—' }}</td></tr>
    <tr><td><strong>Aseguradora:</strong></td><td>{{ $aseguradora }}</td></tr>
    <tr><td><strong>Póliza:</strong></td><td>{{ $poliza }}</td></tr>
    <tr><td><strong>Cobertura:</strong></td><td>{{ $cobertura }}</td></tr>
  </table>
</div>

<div class="section">
  <h3>Detalle del Servicio</h3>
  <table>
    <tr><td width="30%"><strong>Servicio:</strong></td><td>{{ $servicio }}</td></tr>
    <tr><td><strong>Tipo:</strong></td><td>{{ $tipo_servicio }}</td></tr>
    <tr><td><strong>Origen:</strong></td><td>{{ $origen }}</td></tr>
    <tr><td><strong>Destino:</strong></td><td>{{ $destino }}</td></tr>
  </table>
</div>

<div class="section">
  <h3>Desglose de Costos</h3>
  <table>
    <tr><td>Banderazo Base</td><td align="right">${{ $banderazo }}</td></tr>
    <tr><td>KM Incluidos</td><td align="right">{{ $km_incluidos }} km</td></tr>
    <tr><td>KM Recorridos</td><td align="right">{{ $km_recorridos }} km</td></tr>
    <tr><td>KM Excedentes ({{ $km_excedentes }} km × ${{ $costo_km }}/km)</td><td align="right">${{ $costo_km_extra_total }}</td></tr>
    <tr><td>Descuento ({{ $descuento_pct }}%)</td><td align="right">-${{ $monto_descuento }}</td></tr>
    <tr><td><strong>Subtotal</strong></td><td align="right"><strong>${{ $subtotal }}</strong></td></tr>
    <tr><td>IVA (16%)</td><td align="right">${{ $iva }}</td></tr>
    <tr class="total-row"><td><strong>TOTAL</strong></td><td align="right"><strong>${{ $total }}</strong></td></tr>
  </table>
</div>

<div class="footer">
  <p>Este documento es una representación digital de su factura.</p>
  <p>&copy; {{ date('Y') }} SIGESGA - Todos los derechos reservados</p>
</div>
</body>
</html>
