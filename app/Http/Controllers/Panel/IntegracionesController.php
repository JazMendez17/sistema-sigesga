<?php

// Controlador de integraciones con servicios externos (exclusivo Super Administrador)

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\EmpresaIntegracione;
use App\Models\PagoCotizacionIntento;
use App\Services\MercadoPagoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class IntegracionesController extends Controller
{
    // Lista de integraciones dinámicas disponibles
    public function index()
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;
        $ultimaReferenciaPago = PagoCotizacionIntento::where('empresa_id', $empresaId)
            ->latest('id')
            ->value('referencia');

        $base = [
            'mercadopago' => 'Cobros en línea mediante Mercado Pago Checkout Pro con pago alojado.',
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
            'gmail' => 'Gmail',
            'whatsapp' => 'WhatsApp Business',
        ];

        $integraciones = EmpresaIntegracione::where('empresa_id', $empresaId)
            ->whereIn('proveedor', array_keys($base))
            ->orderByRaw("CASE proveedor WHEN 'mercadopago' THEN 0 WHEN 'gmail' THEN 1 ELSE 2 END")
            ->get()
            ->map(function (EmpresaIntegracione $i) use ($titulos, $base, $ultimaReferenciaPago) {
                $cfg = is_array($i->configuracion_json) ? $i->configuracion_json : [];

                return [
                    'id' => $i->id,
                    'proveedor' => $i->proveedor,
                    'titulo' => $titulos[$i->proveedor] ?? ucfirst($i->proveedor),
                    'descripcion' => $i->proveedor === 'mercadopago'
                        ? $base['mercadopago']
                        : ($cfg['descripcion'] ?? ''),
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
                        'modo' => $cfg['modo'] ?? 'pruebas',
                        'webhook_secret_configurada' => $i->proveedor === 'mercadopago'
                            && !empty($i->webhook_secret),
                        'webhook_url' => $i->proveedor === 'mercadopago'
                            ? MercadoPagoService::urlWebhook($ultimaReferenciaPago ?: 'REFERENCIA')
                            : null,
                        'webhook_tiene_intento' => $ultimaReferenciaPago !== null,
                        'webhook_https' => $i->proveedor !== 'mercadopago'
                            || MercadoPagoService::urlWebhook('REFERENCIA') !== null,
                        'ambiente_valido' => $i->proveedor !== 'mercadopago'
                            || empty($i->api_key)
                            || MercadoPagoService::credencialesCoincidenConModo(
                                (string) $i->api_key,
                                (string) ($cfg['modo'] ?? 'pruebas')
                            ),
                        // Gmail
                        'correo' => $cfg['correo'] ?? '',
                        'nombre_remitente' => $cfg['nombre_remitente'] ?? (Auth::user()->empresa?->siglas ?: 'SIGESGA'),
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
            'modo' => 'nullable|in:pruebas,produccion',
            'webhook_secret' => 'nullable|string|min:16|max:500',
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

        if ($integracion->proveedor === 'mercadopago' && $request->filled('webhook_secret')) {
            $update['webhook_secret'] = trim((string) $data['webhook_secret']);
        }

        $cfg = is_array($integracion->configuracion_json) ? $integracion->configuracion_json : [];

        $camposOpcionales = match ($integracion->proveedor) {
            'mercadopago' => ['modo'],
            'gmail' => ['correo', 'nombre_remitente'],
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

        if ($integracion->proveedor === 'mercadopago') {
            $apiKey = $request->filled('api_key') ? trim((string) $data['api_key']) : (string) ($integracion->api_key ?? '');
            $modo = (string) ($cfg['modo'] ?? 'pruebas');
            $activo = $request->has('activo') ? $request->boolean('activo') : (bool) $integracion->activo;

            if ($activo && $apiKey === '') {
                throw ValidationException::withMessages([
                    'api_key' => 'Para activar Mercado Pago debes guardar el Access Token de la aplicación.',
                ]);
            }

            if ($apiKey !== '' && !MercadoPagoService::credencialesCoincidenConModo($apiKey, $modo)) {
                throw ValidationException::withMessages([
                    'modo' => 'El Access Token no es válido para Checkout Pro. Usa el token APP_USR- de la aplicación y selecciona el mismo entorno del panel de Mercado Pago.',
                ]);
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
            'mercadopago' => !empty($integracion->api_key)
                && MercadoPagoService::credencialesCoincidenConModo(
                    (string) $integracion->api_key,
                    (string) ($cfg['modo'] ?? 'pruebas')
                ),
            'gmail' => !empty($cfg['correo']),
            'whatsapp' => !empty($cfg['phone_number_id']) || !empty($cfg['telefono']),
            default => true,
        };
    }
}
