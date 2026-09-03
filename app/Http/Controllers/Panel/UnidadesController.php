<?php

// Controlador de unidades

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Support\Auditoria;
use App\Models\Unidade;
use App\Models\Operadore;
use App\Models\Empleado;
use App\Models\Oficina;
use App\Http\Requests\Panel\StoreUnidadRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UnidadesController extends Controller
{
    // Lista de unidades
    public function index()
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        $unidades = Unidade::with('operadorAsignado.empleado')
            ->where('empresa_id', $empresaId)
            ->latest()
            ->get()
            ->map(fn ($u) => [
                'id' => $u->id,
                'marca' => $u->marca ?? '—',
                'tipo' => $u->tipo ?? '—',
                'modelo' => $u->modelo ?? '—',
                'placas' => $u->placas ?? '—',
                'numero_economico' => $u->numero_economico ?? '—',
                'seguro_vencimiento' => $u->seguro_vencimiento ?? '—',
                'estado_emplacado' => $u->estado_emplacado ?? '—',
                'activo' => $u->activo ?? true,
                'oficina_id' => $u->oficina_id,
                'operador_asignado_id' => $u->operador_asignado_id,
                'operador' => $u->operadorAsignado?->empleado?->nombre ?? '—',
            ]);

        return Inertia::render('Panel/Unidades/Index', [
            'unidades' => $unidades,
        ]);
    }

    // Formulario para crear unidad
    public function create()
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        return Inertia::render('Panel/Unidades/Create', [
            'operadores' => Empleado::where('empresa_id', $empresaId)
                ->where('puesto', 'like', '%operador%')
                ->get()
                ->map(fn ($e) => [
                    'id' => $e->operador?->id ?? null,
                    'empleado_id' => $e->id,
                    'nombre_operador' => trim($e->nombre . ' ' . ($e->apellido_paterno ?? '') . ' ' . ($e->apellido_materno ?? '')),
                    'disponible' => $e->operador?->disponible ?? true,
                ])
                ->filter(fn ($o) => $o['id'] !== null)
                ->values(),
            'oficinas' => Oficina::where('empresa_id', $empresaId)->get(['id', 'nombre']),
        ]);
    }

    // Guardar unidad en base de datos
    public function store(StoreUnidadRequest $request)
    {
        $user = Auth::user();

        $data = $request->validated();
        $data['empresa_id'] = $user->empresa_id;
        $data['activo'] = $request->boolean('activo');

        Unidade::create($data);

        return redirect()->route('panel.unidades.index')
            ->with('success', 'Unidad creada correctamente');
    }

    // Ver detalle de unidad
    public function show($id)
    {
        return Inertia::render('Panel/Unidades/Show', [
            'unidad' => Unidade::with('operadorAsignado.empleado', 'oficina')->where('empresa_id', auth()->user()->empresa_id)->findOrFail($id),
        ]);
    }

    // Formulario para editar unidad
    public function edit($id)
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        return Inertia::render('Panel/Unidades/Create', [
            'unidad' => Unidade::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id),
            'operadores' => Empleado::where('empresa_id', $empresaId)
                ->where('puesto', 'like', '%operador%')
                ->get()
                ->map(fn ($e) => [
                    'id' => $e->operador?->id ?? null,
                    'empleado_id' => $e->id,
                    'nombre_operador' => trim($e->nombre . ' ' . ($e->apellido_paterno ?? '') . ' ' . ($e->apellido_materno ?? '')),
                    'disponible' => $e->operador?->disponible ?? true,
                ])
                ->filter(fn ($o) => $o['id'] !== null)
                ->values(),
            'oficinas' => Oficina::where('empresa_id', $empresaId)->get(['id', 'nombre']),
        ]);
    }

    // Actualizar datos de la unidad
    public function update(StoreUnidadRequest $request, $id)
    {
        $unidad = Unidade::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        $data = $request->validated();
        $data['activo'] = $request->boolean('activo');
        $unidad->update($data);

        return redirect()->route('panel.unidades.index')
            ->with('success', 'Unidad actualizada correctamente');
    }

    // Eliminar unidad
    public function destroy($id)
    {
        $unidad = Unidade::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        $unidad->unidadMantenimientos()->get()->each(fn($x) => $x->eliminar());
        $unidad->servicios()->whereIn('estado', ['asignado', 'inicio_servicio', 'en_sitio_origen', 'salida_destino', 'en_destino'])->get()->each(fn($x) => $x->eliminar());

        Auditoria::registrar($unidad);

        $unidad->eliminar();

        return redirect()->route('panel.unidades.index')
            ->with('success', 'Unidad eliminada correctamente');
    }
}
