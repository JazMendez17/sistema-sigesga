<?php

// Controlador de configuración general (borrado físico seguro y validación de rutas de archivos)

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\EmpresaNosotros;
use App\Support\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ConfiguracionController extends Controller
{
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
            'direccion' => 'nullable|string|max:500',
            'color_primario' => 'nullable|string|max:20',
            'color_secundario' => 'nullable|string|max:20',
            'color_fondo' => 'nullable|string|max:20',
            'color_texto' => 'nullable|string|max:20',
            'tipografia' => 'nullable|string|max:100',
            'modo_oscuro' => 'nullable|boolean',
            'logo' => ['nullable', 'string', 'max:500', 'not_regex:/\.\./'],
            'imagen_fondo' => ['nullable', 'string', 'max:500', 'not_regex:/\.\./'],
            'nosotros' => 'nullable|array',
            'nosotros.quienes_somos' => 'nullable|string|max:5000',
            'nosotros.mision' => 'nullable|string|max:5000',
            'nosotros.vision' => 'nullable|string|max:5000',
            'valores' => 'nullable|array',
            'valores.*.valor' => 'nullable|string|max:100',
            'valores.*.descripcion' => 'nullable|string|max:255',
            'servicios_landing' => 'nullable|array',
            'servicios_landing.*.id' => 'nullable|integer',
            'servicios_landing.*.tipo' => 'nullable|string|max:100',
            'servicios_landing.*.descripcion' => 'nullable|string|max:1000',
            'servicios_landing.*.foto' => ['nullable', 'string', 'max:255', 'not_regex:/\.\./'],
            'modulo_colores' => 'nullable|array',
            'modulo_colores.*' => 'nullable|string|max:20|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $data['modo_oscuro'] = $request->boolean('modo_oscuro');

        DB::transaction(function () use ($request, $empresa, $data) {
            $empresa->update($data);

            $nosotrosData = $request->input('nosotros', []);
            EmpresaNosotros::updateOrCreate(
                ['empresa_id' => $empresa->id],
                [
                    'quienes_somos' => $nosotrosData['quienes_somos'] ?? '',
                    'mision' => $nosotrosData['mision'] ?? '',
                    'vision' => $nosotrosData['vision'] ?? '',
                ]
            );

            $valoresData = $request->input('valores', []);
            if ($request->has('valores')) {
                foreach ($empresa->empresaValores()->get() as $valor) {
                    Auditoria::registrar($valor);
                    $valor->delete();
                }
                foreach ($valoresData as $i => $v) {
                    if (filled($v['valor'] ?? null)) {
                        $empresa->empresaValores()->create([
                            'valor' => $v['valor'],
                            'descripcion' => $v['descripcion'] ?? '',
                            'orden' => $i,
                        ]);
                    }
                }
            }

            if ($request->has('servicios_landing')) {
                $serviciosData = $request->input('servicios_landing', []);
                $existentes = $empresa->empresaServicios()->get()->keyBy('id');
                $idsConservados = [];

                foreach ($serviciosData as $i => $s) {
                    $existente = !empty($s['id']) ? $existentes->get((int) $s['id']) : null;
                    // Solo se permite conservar la foto propia existente o una subida a servicios/empresa
                    $foto = $existente?->foto;
                    if (!empty($s['foto']) && (str_starts_with($s['foto'], 'empresa/') || str_starts_with($s['foto'], 'servicios/'))) {
                        $foto = $s['foto'];
                    }

                    if ($existente) {
                        $existente->update([
                            'tipo' => $s['tipo'] ?? $existente->tipo,
                            'descripcion' => $s['descripcion'] ?? '',
                            'foto' => $foto,
                            'orden' => $i,
                        ]);
                        $idsConservados[] = $existente->id;
                    } elseif (filled($s['tipo'] ?? null)) {
                        $creado = $empresa->empresaServicios()->create([
                            'tipo' => $s['tipo'],
                            'descripcion' => $s['descripcion'] ?? '',
                            'foto' => $foto,
                            'orden' => $i,
                            'activo' => true,
                        ]);
                        $idsConservados[] = $creado->id;
                    }
                }

                foreach ($existentes as $idExistente => $servicioEliminado) {
                    if (!in_array($idExistente, $idsConservados, true)) {
                        Auditoria::registrar($servicioEliminado);
                        $servicioEliminado->delete();
                    }
                }
            }

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
        });

        return back()->with('success', 'Configuración guardada correctamente');
    }
}
