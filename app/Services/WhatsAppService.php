<?php

// Servicio de envío de notificaciones por WhatsApp Business Cloud API usando Integraciones

namespace App\Services;

use App\Models\EmpresaIntegracione;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /** Envía un mensaje de texto por WhatsApp si la integración está activa y configurada. */
    public function enviarTexto(int $empresaId, ?string $telefonoDestino, string $mensaje): bool
    {
        $telefono = $this->normalizarTelefono($telefonoDestino);
        if ($telefono === null) {
            return false;
        }

        $integracion = EmpresaIntegracione::where('empresa_id', $empresaId)
            ->where('proveedor', 'whatsapp')
            ->where('activo', true)
            ->first();

        if (!$integracion || empty($integracion->api_key)) {
            return false;
        }

        $cfg = is_array($integracion->configuracion_json) ? $integracion->configuracion_json : [];
        $phoneNumberId = trim((string) ($cfg['phone_number_id'] ?? ''));
        if ($phoneNumberId === '') {
            return false;
        }

        try {
            $response = Http::withToken($integracion->api_key)
                ->timeout(15)
                ->post("https://graph.facebook.com/v21.0/{$phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to' => $telefono,
                    'type' => 'text',
                    'text' => [
                        'preview_url' => false,
                        'body' => $mensaje,
                    ],
                ]);

            if ($response->failed()) {
                Log::warning('Error al enviar mensaje de WhatsApp', [
                    'empresa_id' => $empresaId,
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Excepción al enviar mensaje de WhatsApp', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function normalizarTelefono(?string $telefono): ?string
    {
        $digitos = preg_replace('/\D+/', '', (string) $telefono) ?? '';
        if (strlen($digitos) === 10) {
            // Asume México (+52) cuando son 10 dígitos nacionales
            return '52' . $digitos;
        }
        if (strlen($digitos) >= 11 && strlen($digitos) <= 15) {
            return $digitos;
        }

        return null;
    }
}
