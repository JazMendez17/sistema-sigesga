<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura {{ $folio }}</title>
</head>
<body style="margin:0;padding:0;background-color:#E8EDF2;font-family:Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#E8EDF2;padding:30px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.08);">
                    <!-- Encabezado -->
                    <tr>
                        <td style="background:#4F46E5;padding:30px;text-align:center;">
                            <h1 style="color:#ffffff;margin:0;font-size:24px;font-weight:700;">SIGESGA</h1>
                            <p style="color:rgba(255,255,255,0.85);margin:8px 0 0;font-size:14px;">Sistema de Gestión de Grúas y Servicios</p>
                        </td>
                    </tr>

                    <!-- Cuerpo -->
                    <tr>
                        <td style="padding:30px;">
                            <h2 style="color:#1F2937;font-size:20px;margin:0 0 8px;">Factura {{ $folio }}</h2>
                            <p style="color:#6B7280;font-size:14px;margin:0 0 24px;">Fecha de emisión: {{ $fecha }}</p>

                            <p style="color:#374151;font-size:15px;margin:0 0 24px;">
                                Estimado(a) <strong>{{ $cliente }}</strong>, a continuación se presentan los detalles de su factura:
                            </p>

                            <!-- Tabla de detalles -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #E5E7EB;border-radius:12px;overflow:hidden;">
                                <tr>
                                    <td style="padding:16px 20px;background:#F9FAFB;border-bottom:1px solid #E5E7EB;">
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="color:#6B7280;font-size:13px;">Concepto</td>
                                                <td style="color:#6B7280;font-size:13px;text-align:right;">Importe</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:16px 20px;border-bottom:1px solid #E5E7EB;">
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="color:#374151;font-size:14px;">Servicio de grúa - {{ $folio }}</td>
                                                <td style="color:#374151;font-size:14px;text-align:right;">${{ $subtotal }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:16px 20px;border-bottom:1px solid #E5E7EB;">
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="color:#374151;font-size:14px;">IVA (16%)</td>
                                                <td style="color:#374151;font-size:14px;text-align:right;">${{ $iva }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:16px 20px;background:#F9FAFB;">
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="color:#1F2937;font-size:16px;font-weight:700;">Total</td>
                                                <td style="color:#1F2937;font-size:16px;font-weight:700;text-align:right;">${{ $total }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <p style="color:#9CA3AF;font-size:12px;margin:20px 0 0;text-align:center;">
                                Este correo fue generado automáticamente por SIGESGA.<br>
                                Si tienes dudas, contacta a soporte.
                            </p>
                        </td>
                    </tr>

                    <!-- Pie de página -->
                    <tr>
                        <td style="background:#F3F4F6;padding:20px;text-align:center;">
                            <p style="color:#9CA3AF;font-size:12px;margin:0;">
                                &copy; {{ date('Y') }} SIGESGA - Todos los derechos reservados
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
