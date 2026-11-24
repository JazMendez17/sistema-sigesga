<?php

// Aplica dinámicamente la configuración de Correo (Gmail/SMTP) guardada en Integraciones cuando está activa.

namespace App\Http\Middleware;

use App\Models\EmpresaIntegracione;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ConfigureCompanyMail
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $empresaId = Auth::user()?->empresa_id;
            $query = EmpresaIntegracione::where('proveedor', 'gmail')->where('activo', true);
            if ($empresaId) {
                $query->where('empresa_id', $empresaId);
            }
            $gmail = $query->first();

            if ($gmail && !empty($gmail->api_key)) {
                $cfg = is_array($gmail->configuracion_json) ? $gmail->configuracion_json : [];
                $correo = trim((string) ($cfg['correo'] ?? ''));

                if ($correo !== '') {
                    $nombre = trim((string) ($cfg['nombre_remitente'] ?? config('app.name', 'SIGESGA')));

                    config([
                        'mail.default' => 'smtp',
                        'mail.mailers.smtp.host' => 'smtp.gmail.com',
                        'mail.mailers.smtp.port' => 587,
                        'mail.mailers.smtp.username' => $correo,
                        'mail.mailers.smtp.password' => $gmail->api_key,
                        'mail.mailers.smtp.encryption' => 'tls',
                        'mail.from.address' => $correo,
                        'mail.from.name' => $nombre,
                    ]);
                }
            }
        } catch (\Throwable) {
            // Si la tabla aún no existe o falla la consulta, conserva la configuración base de correo.
        }

        return $next($request);
    }
}
