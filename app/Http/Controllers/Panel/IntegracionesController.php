<?php

// Controlador de integraciones con servicios externos (exclusivo Super Administrador)

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\EmpresaIntegracione;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class IntegracionesController extends Controller
{
    // Lista de integraciones dinámicas disponibles
    public function index()
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        $base = [
            'mercadopago' => 'Cobros con tarjeta mediante Mercado Pago Checkout API (Orders).',
            'gmail' => 'Envío de cotizaciones, facturas, códigos de desbloqueo y correos del sistema.',
            'whatsapp' => 'Envío de notificaciones y comprobantes por WhatsApp Business.',
        ];

        foreach ($base as $proveedor => $descripcion) {
            EmpresaIntegracione::firstOrCreate(
                ['empresa_id' => $empresaId, 'proveedor' => $proveedor],
                ['configuracion_json' => ['descripcion' => $descripcion], 'activo' => false]
            );
        }

        $titulos = [
            'mercadopago' => 'Mercado Pago',
            'gmail' => 'Correo electrónico (Gmail)',
            'whatsapp' => 'WhatsApp Business',
        ];

        $integraciones = EmpresaIntegracione::where('empresa_id', $empresaId)
            ->whereIn('proveedor', array_keys($base))
            ->orderByRaw("CASE proveedor WHEN 'mercadopago' THEN 0 WHEN 'gmail' THEN 1 ELSE 2 END")
            ->get()
            ->map(function (EmpresaIntegracione $i) use ($titulos) {
                $cfg = is_array($i->configuracion_json) ? $i->configuracion_json : [];

                return [
                    'id' => $i->id,
                    'proveedor' => $i->proveedor,
                    'titulo' => $titulos[$i->proveedor] ?? ucfirst($i->proveedor),
                    'descripcion' => $cfg['descripcion'] ?? '',
                    'icono' => match ($i->proveedor) {
                        'mercadopago' => 'card',
                        'gmail' => 'mail',
                        'whatsapp' => 'whatsapp',
                        default => 'puzzle',
                    },
                    'activo' => (bool) ($i->activo ?? false),
                    'configurado' => $this->estaConfigurada($i, $cfg),
                    'config' => [
                        // Mercado Pago
                        'public_key' => $cfg['public_key'] ?? '',
                        'modo' => $cfg['modo'] ?? 'pruebas',
                        // Gmail / SMTP
                        'correo' => $cfg['correo'] ?? '',
                        'nombre_remitente' => $cfg['nombre_remitente'] ?? (Auth::user()->empresa?->siglas ?: 'SIGESGA'),
                        'servidor' => $cfg['servidor'] ?? 'smtp.gmail.com',
                        'puerto' => (int) ($cfg['puerto'] ?? 587),
                        // WhatsApp
                        'telefono' => $cfg['telefono'] ?? '',
                        'phone_number_id' => $cfg['phone_number_id'] ?? '',
                    ],
                ];
            });

        return Inertia::render('Panel/Integraciones/Index', [
            'integraciones' => $integraciones,
        ]);
    }

    // Actualizar configuración de integración
    public function update(Request $request, $id)
    {
        $integracion = EmpresaIntegracione::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        $data = $request->validate([
            'activo' => 'nullable|boolean',
            'api_key' => 'nullable|string|max:500',
            // Mercado Pago
            'public_key' => 'nullable|string|max:500',
            'modo' => 'nullable|in:pruebas,produccion',
            // Gmail / Correo
            'correo' => 'nullable|email|max:255',
            'nombre_remitente' => 'nullable|string|max:120',
            'servidor' => 'nullable|string|max:150',
            'puerto' => 'nullable|integer|between:1,65535',
            // WhatsApp
            'telefono' => 'nullable|string|max:30',
            'phone_number_id' => 'nullable|string|max:120',
        ], [
            'correo.email' => 'Ingresa un correo electrónico válido.',
            'puerto.between' => 'El puerto debe estar entre 1 y 65535.',
        ]);

        $update = [
            'activo' => $request->has('activo') ? $request->boolean('activo') : $integracion->activo,
        ];

        if ($request->filled('api_key')) {
            $update['api_key'] = trim((string) $data['api_key']);
        }

        $cfg = is_array($integracion->configuracion_json) ? $integracion->configuracion_json : [];

        $camposOpcionales = match ($integracion->proveedor) {
            'mercadopago' => ['public_key', 'modo'],
            'gmail' => ['correo', 'nombre_remitente', 'servidor', 'puerto'],
            'whatsapp' => ['telefono', 'phone_number_id'],
            default => [],
        };

        $cambioConfig = false;
        foreach ($camposOpcionales as $campo) {
            if ($request->has($campo)) {
                $valor = $data[$campo] ?? null;
                $cfg[$campo] = is_string($valor) ? trim($valor) : $valor;
                $cambioConfig = true;
            }
        }

        if ($cambioConfig) {
            $update['configuracion_json'] = $cfg;
        }

        $integracion->update($update);

        return back()->with('success', 'Integración actualizada correctamente.');
    }

    private function estaConfigurada(EmpresaIntegracione $integracion, array $cfg): bool
    {
        if (empty($integracion->api_key)) {
            return false;
        }

        return match ($integracion->proveedor) {
            'mercadopago' => !empty($cfg['public_key']),
            'gmail' => !empty($cfg['correo']),
            'whatsapp' => !empty($cfg['phone_number_id']) || !empty($cfg['telefono']),
            default => true,
        };
    }
}
