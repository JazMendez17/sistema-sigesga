<?php

// Controlador de integraciones con servicios externos

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\EmpresaIntegracione;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class IntegracionesController extends Controller
{
    // Lista de integraciones disponibles
    public function index()
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        // Crea las integraciones base si la empresa aún no las tiene, para que
        // el módulo siempre muestre contenido.
        $base = [
            'mercadopago' => 'Cobros con tarjeta mediante Mercado Pago Checkout API (Orders).',
            'whatsapp' => 'Envío de notificaciones y comprobantes por WhatsApp.',
            'gmail' => 'Envío de cotizaciones, facturas y correos del sistema.',
            'google_maps' => 'Cálculo de rutas, distancias y tiempos estimados.',
        ];
        foreach ($base as $proveedor => $descripcion) {
            EmpresaIntegracione::firstOrCreate(
                ['empresa_id' => $empresaId, 'proveedor' => $proveedor],
                ['configuracion_json' => ['descripcion' => $descripcion], 'activo' => false]
            );
        }

        $titulos = [
            'mercadopago' => 'Mercado Pago',
            'whatsapp' => 'WhatsApp',
            'gmail' => 'Gmail',
            'google_maps' => 'Google Maps',
            'stripe' => 'Stripe',
        ];

        $integraciones = EmpresaIntegracione::where('empresa_id', $empresaId)
            ->orderByRaw("CASE WHEN proveedor = 'mercadopago' THEN 0 ELSE 1 END")
            ->get()
            ->map(fn ($i) => [
                'id' => $i->id,
                'proveedor' => $i->proveedor,
                'titulo' => $titulos[$i->proveedor] ?? ucfirst($i->proveedor),
                'descripcion' => $i->configuracion_json['descripcion'] ?? '',
                'public_key' => $i->configuracion_json['public_key'] ?? '',
                'icono' => match ($i->proveedor) {
                    'mercadopago', 'stripe' => 'card',
                    'whatsapp' => 'whatsapp',
                    'gmail' => 'mail',
                    'google_maps' => 'map',
                    default => 'puzzle',
                },
                'activo' => (bool) ($i->activo ?? false),
                'configurado' => !empty($i->api_key) || ($i->proveedor === 'mercadopago' && !empty(config('services.mercadopago.access_token'))),
            ]);

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
            'public_key' => 'nullable|string|max:500',
        ]);

        $update = [
            'activo' => $request->has('activo') ? $request->boolean('activo') : $integracion->activo,
        ];

        if ($request->filled('api_key')) {
            $update['api_key'] = trim((string) $data['api_key']);
        }

        if ($request->has('public_key')) {
            $config = is_array($integracion->configuracion_json) ? $integracion->configuracion_json : [];
            $config['public_key'] = trim((string) ($data['public_key'] ?? ''));
            $update['configuracion_json'] = $config;
        }

        $integracion->update($update);

        return back()->with('success', 'Integración actualizada correctamente');
    }
}
