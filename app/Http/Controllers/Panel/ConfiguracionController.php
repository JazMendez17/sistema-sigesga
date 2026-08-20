<?php

// Controlador de configuración general

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Support\Auditoria;
use App\Models\EmpresaNosotros;
use App\Models\EmpresaValore;
use App\Models\EmpresaServicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ConfiguracionController extends Controller
{
    // Mostrar configuración de la empresa
    public function index()
    {
        $user = Auth::user();
        $empresa = $user->empresa;

        return Inertia::render('Panel/Configuracion/Index', [
            'nosotros' => $empresa?->empresaNosotros ?? [
                'quienes_somos' => '',
                'mision' => '',
                'vision' => '',
            ],
            'valores' => $empresa?->empresaValores?->map(fn ($v) => [
                'id' => $v->id,
                'valor' => $v->valor,
                'descripcion' => $v->descripcion,
            ]) ?? [],
            'servicios_landing' => $empresa?->empresaServicios?->map(fn ($s) => [
                'id' => $s->id,
                'tipo' => $s->tipo,
                'descripcion' => $s->descripcion,
                'foto' => $s->foto,
            ]) ?? [],
            'modulo_colores' => $empresa?->empresaModuloColores?->pluck('color', 'modulo')->toArray() ?? [],
        ]);
    }

    // Guardar configuración de la empresa
    public function update(Request $request)
    {
        $user = Auth::user();
        $empresa = $user->empresa;

        if (!$empresa) {
            return back()->with('error', 'No hay empresa asociada');
        }

        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'siglas' => 'required|string|max:20',
            'slogan' => 'nullable|string|max:500',
            'texto_derechos' => 'nullable|string|max:500',
            'telefono_contacto' => 'nullable|string|max:50',
            'email_contacto' => 'nullable|email|max:255',
            'color_primario' => 'nullable|string|max:20',
            'color_secundario' => 'nullable|string|max:20',
            'color_fondo' => 'nullable|string|max:20',
            'color_texto' => 'nullable|string|max:20',
            'tipografia' => 'nullable|string|max:100',
            'modo_oscuro' => 'nullable|boolean',
            'logo' => 'nullable|string|max:500',
            'imagen_fondo' => 'nullable|string|max:500',
            'nosotros' => 'nullable|array',
            'nosotros.quienes_somos' => 'nullable|string',
            'nosotros.mision' => 'nullable|string',
            'nosotros.vision' => 'nullable|string',
            'valores' => 'nullable|array',
            'valores.*.valor' => 'nullable|string|max:100',
            'valores.*.descripcion' => 'nullable|string|max:255',
            'servicios_landing' => 'nullable|array',
            'servicios_landing.*.tipo' => 'nullable|string|max:100',
            'servicios_landing.*.foto' => 'nullable|string|max:255',
            'modulo_colores' => 'nullable|array',
            'modulo_colores.*' => 'nullable|string|max:20|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $data['modo_oscuro'] = $request->boolean('modo_oscuro');

        $empresa->update($data);

        // Guardar Nosotros
        $nosotrosData = $request->input('nosotros', []);
        EmpresaNosotros::updateOrCreate(
            ['empresa_id' => $empresa->id],
            [
                'quienes_somos' => $nosotrosData['quienes_somos'] ?? '',
                'mision' => $nosotrosData['mision'] ?? '',
                'vision' => $nosotrosData['vision'] ?? '',
            ]
        );

        // Guardar Valores
        $valoresData = $request->input('valores', []);
        if (!empty($valoresData)) {
            foreach ($empresa->empresaValores()->get() as $valor) {
                Auditoria::registrar($valor);
            }
            $empresa->empresaValores()->update(['eliminado' => true]);
            foreach ($valoresData as $i => $v) {
                $empresa->empresaValores()->create([
                    'valor' => $v['valor'] ?? '',
                    'descripcion' => $v['descripcion'] ?? '',
                    'orden' => $i,
                ]);
            }
        }

        // Guardar Servicios (landing)
        $serviciosData = $request->input('servicios_landing', []);
        if (!empty($serviciosData)) {
            foreach ($empresa->empresaServicios()->get() as $servicioLanding) {
                Auditoria::registrar($servicioLanding);
            }
            $empresa->empresaServicios()->update(['eliminado' => true]);
            foreach ($serviciosData as $i => $s) {
                $empresa->empresaServicios()->create([
                    'tipo' => $s['tipo'] ?? '',
                    'descripcion' => $s['descripcion'] ?? '',
                    'foto' => $s['foto'] ?? null,
                    'orden' => $i,
                ]);
            }
        }

        // Guardar colores de módulos
        $moduloColores = $request->input('modulo_colores', []);
        if (!empty($moduloColores)) {
            foreach ($moduloColores as $modulo => $color) {
                if (!empty($color)) {
                    $empresa->empresaModuloColores()->updateOrCreate(
                        ['modulo' => $modulo],
                        ['color' => $color]
                    );
                }
            }
        }

        return back()->with('success', 'Configuración guardada correctamente');
    }
}