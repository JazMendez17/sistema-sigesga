<?php

namespace App\Http\Controllers;

use App\Models\Contacto;
use App\Models\Empresa;
use App\Models\Notificacione;
use App\Models\Servicio;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LandingController extends Controller
{
    public function index()
    {
        $empresa = Empresa::first();
        $redesSociales = $empresa
            ? $empresa->empresaAccesosRapidos()
                ->where('activo', true)
                ->orderBy('orden')
                ->get()
                ->map(fn ($red) => [
                    'titulo' => $red->titulo,
                    'descripcion' => $red->descripcion,
                    'link' => $red->link,
                    'imagen' => $red->imagen,
                    'icono' => $red->icono,
                ])
            : [];
        $servicios = $empresa
            ? $empresa->empresaServicios()
                ->where('activo', true)
                ->orderBy('orden')
                ->get()
                ->map(fn ($servicio) => [
                    'id' => $servicio->id,
                    'tipo' => $servicio->tipo,
                    'descripcion' => $servicio->descripcion,
                    'foto' => $servicio->foto,
                    'color' => $servicio->color ?: ($empresa->color_primario ?: '#4F46E5'),
                ])
            : [];
        $nosotros = $empresa?->empresaNosotros
            ? [
                'quienes_somos' => $empresa->empresaNosotros->quienes_somos,
                'mision' => $empresa->empresaNosotros->mision,
                'vision' => $empresa->empresaNosotros->vision,
            ]
            : null;
        $valores = $empresa
            ? $empresa->empresaValores()
                ->orderBy('orden')
                ->get()
                ->map(fn ($v) => [
                    'valor' => $v->valor,
                    'descripcion' => $v->descripcion,
                ])
            : [];

        return Inertia::render('Landing/Index', [
            'redesSociales' => $redesSociales,
            'servicios' => $servicios,
            'nosotros' => $nosotros,
            'valores' => $valores,
        ]);
    }

    public function solicitar()
    {
        return Inertia::render('Landing/SolicitarServicio');
    }

    public function solicitarStore(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'nullable|string|max:150',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:150',
            'ubicacion' => 'required|string|max:255',
            'tipo_servicio' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
        ]);

        $resumen = "[SOLICITUD WEB]\n"
            . "Servicio: {$validated['tipo_servicio']}\n"
            . "Ubicación: {$validated['ubicacion']}"
            . (!empty($validated['telefono']) ? "\nTeléfono: {$validated['telefono']}" : '')
            . (!empty($validated['descripcion']) ? "\nDetalles: {$validated['descripcion']}" : '');

        Contacto::create([
            'nombre' => ($validated['nombre'] ?? null) ?: 'Solicitud desde Landing',
            'email' => ($validated['email'] ?? null) ?: 'solicitud@sigesga.com',
            'mensaje' => $resumen,
        ]);

        $empresa = Empresa::first();
        if ($empresa) {
            $destinatarios = Usuario::where('empresa_id', $empresa->id)
                ->whereIn('rol', ['superadmin', 'admin', 'cotizador'])
                ->pluck('id');

            foreach ($destinatarios as $uid) {
                Notificacione::create([
                    'empresa_id' => $empresa->id,
                    'usuario_id' => $uid,
                    'mensaje' => "Nueva solicitud web de {$validated['tipo_servicio']} en {$validated['ubicacion']}.",
                    'canal' => 'sistema_push',
                    'estado' => 'pendiente',
                ]);
            }
        }

        return redirect()->route('solicitar')->with('success', 'Solicitud enviada correctamente. Nuestro equipo te contactará a la brevedad.');
    }

    public function rastrear(Request $request)
    {
        $folio = strtoupper(trim((string) $request->query('folio', '')));
        $resultado = null;

        if ($folio !== '') {
            $servicio = null;
            if (preg_match('/^SVC-0*(\d+)$/i', $folio, $m)) {
                $servicio = Servicio::with(['cotizacion.tipoServicio', 'bitacoraTiemposServicio'])->find((int) $m[1]);
            } else {
                $servicio = Servicio::with(['cotizacion.tipoServicio', 'bitacoraTiemposServicio'])
                    ->whereHas('cotizacion', fn ($q) => $q->where('folio', $folio))
                    ->first();
            }

            if ($servicio) {
                // Solo exponer estado operativo (sin PII del cliente ni del operador)
                $resultado = [
                    'folio' => 'SVC-' . str_pad((string) $servicio->id, 5, '0', STR_PAD_LEFT),
                    'cotizacion_folio' => $servicio->cotizacion?->folio,
                    'tipo_servicio' => $servicio->cotizacion?->tipoServicio?->nombre ?? 'Asistencia vial',
                    'estado' => $servicio->estado,
                    'fecha' => $servicio->created_at?->format('d/m/Y H:i'),
                    'bitacora' => $servicio->bitacoraTiemposServicio ? [
                        'asignado' => $servicio->bitacoraTiemposServicio->hora_asignado,
                        'inicio' => $servicio->bitacoraTiemposServicio->hora_inicio_servicio,
                        'en_sitio' => $servicio->bitacoraTiemposServicio->hora_en_sitio_origen,
                        'en_destino' => $servicio->bitacoraTiemposServicio->hora_en_destino,
                        'finalizado' => $servicio->bitacoraTiemposServicio->hora_finalizado,
                    ] : null,
                ];
            }
        }

        return Inertia::render('Landing/RastrearServicio', [
            'folioBuscado' => $folio,
            'resultado' => $resultado,
        ]);
    }

    public function soporte()
    {
        return Inertia::render('Landing/Soporte');
    }
}
