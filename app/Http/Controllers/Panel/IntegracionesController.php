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

        $integraciones = EmpresaIntegracione::where('empresa_id', $empresaId)
            ->get()
            ->map(fn ($i) => [
                'id' => $i->id,
                'titulo' => ucfirst($i->proveedor),
                'descripcion' => $i->configuracion_json['descripcion'] ?? '',
                'icono' => match ($i->proveedor) {
                    'whatsapp' => 'message-circle',
                    'gmail' => 'mail',
                    'google_maps' => 'map',
                    default => 'puzzle',
                },
                'activo' => $i->activo ?? false,
                'configurado' => !empty($i->api_key),
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
        ]);

        $data['activo'] = $request->boolean('activo');
        $integracion->update($data);

        return back()->with('success', 'Integración actualizada correctamente');
    }
}
