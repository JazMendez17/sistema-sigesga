<?php

// Controlador de empleados

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Empleado;
use App\Models\Oficina;
use App\Models\Direccion;
use App\Http\Requests\Panel\StoreEmpleadoRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class EmpleadosController extends Controller
{
    // Lista de empleados
    public function index()
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        $empleados = Empleado::with('oficina')
            ->where('empresa_id', $empresaId)
            ->latest()
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'nombre_completo' => trim($e->nombre . ' ' . ($e->apellido_paterno ?? '') . ' ' . ($e->apellido_materno ?? '')),
                'curp' => $e->curp ?? '—',
                'correo' => $e->correo ?? '—',
                'telefono' => $e->telefono ?? '—',
                'puesto' => $e->puesto ?? '—',
                'oficina' => $e->oficina?->nombre ?? '—',
            ]);

        return Inertia::render('Panel/Empleados/Index', [
            'empleados' => $empleados,
        ]);
    }

    // Formulario para crear empleado
    public function create()
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        return Inertia::render('Panel/Empleados/Create', [
            'oficinas' => Oficina::where('empresa_id', $empresaId)->get(['id', 'nombre']),
        ]);
    }

    // Guardar empleado en base de datos
    public function store(StoreEmpleadoRequest $request)
    {
        $user = Auth::user();

        $data = $request->validated();
        $data['empresa_id'] = $user->empresa_id;

        if (!empty($data['direccion']) && array_filter($data['direccion'])) {
            $direccion = Direccion::create($data['direccion']);
            $data['direccion_id'] = $direccion->id;
        }

        unset($data['direccion']);

        Empleado::create($data);

        return redirect()->route('panel.empleados.index')
            ->with('success', 'Empleado creado correctamente');
    }

    // Ver detalle de empleado
    public function show($id)
    {
        return Inertia::render('Panel/Empleados/Show', [
            'empleado' => Empleado::with('oficina', 'direccion')->where('empresa_id', auth()->user()->empresa_id)->findOrFail($id),
        ]);
    }

    // Formulario para editar empleado
    public function edit($id)
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        return Inertia::render('Panel/Empleados/Create', [
            'empleado' => Empleado::with('direccion')->where('empresa_id', auth()->user()->empresa_id)->findOrFail($id),
            'oficinas' => Oficina::where('empresa_id', $empresaId)->get(['id', 'nombre']),
        ]);
    }

    // Actualizar datos del empleado
    public function update(StoreEmpleadoRequest $request, $id)
    {
        $empleado = Empleado::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        $data = $request->validated();

        if (!empty($data['direccion']) && array_filter($data['direccion'])) {
            if ($empleado->direccion_id) {
                $empleado->direccion->update($data['direccion']);
            } else {
                $direccion = Direccion::create($data['direccion']);
                $data['direccion_id'] = $direccion->id;
            }
        }

        unset($data['direccion']);

        $empleado->update($data);

        return redirect()->route('panel.empleados.index')
            ->with('success', 'Empleado actualizado correctamente');
    }

    // Eliminar empleado
    public function destroy($id)
    {
        $empleado = Empleado::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        if ($empleado->operador || $empleado->usuario) {
            return redirect()->back()->with('error', 'No se puede eliminar el empleado porque está vinculado a un operador o usuario.');
        }

        $empleado->delete();

        return redirect()->route('panel.empleados.index')
            ->with('success', 'Empleado eliminado correctamente');
    }
}
