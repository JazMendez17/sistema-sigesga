<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><title>Nuevo mensaje de contacto</title>
<style>
  body { font-family: Arial, sans-serif; margin: 0; padding: 20px; color: #1F2937; }
  .header { text-align: center; border-bottom: 2px solid #4F46E5; padding-bottom: 15px; margin-bottom: 20px; }
  .header h1 { color: #4F46E5; margin: 0; font-size: 24px; }
  .header p { margin: 5px 0; color: #6B7280; font-size: 13px; }
  .section { margin-bottom: 15px; }
  .section h3 { color: #4F46E5; border-bottom: 1px solid #E5E7EB; padding-bottom: 5px; font-size: 14px; }
  table { width: 100%; border-collapse: collapse; margin: 10px 0; }
  td { padding: 8px; font-size: 13px; border-bottom: 1px solid #E5E7EB; }
  .mensaje-box { background: #F3F4F6; border-radius: 8px; padding: 16px; margin: 10px 0; font-size: 14px; line-height: 1.6; }
  .footer { margin-top: 30px; text-align: center; font-size: 11px; color: #9CA3AF; border-top: 1px solid #E5E7EB; padding-top: 15px; }
</style></head>
<body>
<div class="header">
  <h1>SIGESGA</h1>
  <p>Sistema de Gestión de Grúas y Servicios</p>
</div>

<div class="section">
  <h3>Nuevo mensaje de contacto</h3>
  <table>
    <tr><td width="30%"><strong>Nombre:</strong></td><td>{{ $nombre }}</td></tr>
    <tr><td><strong>Email:</strong></td><td>{{ $email }}</td></tr>
    <tr><td><strong>Fecha:</strong></td><td>{{ $fecha }}</td></tr>
  </table>
</div>

<div class="section">
  <h3>Mensaje</h3>
  <div class="mensaje-box">{{ $mensaje }}</div>
</div>

<div class="footer">
  <p>Este mensaje fue enviado desde la sección de Contacto de la página web.</p>
  <p>&copy; {{ date('Y') }} SIGESGA - Todos los derechos reservados</p>
</div>
</body>
</html>