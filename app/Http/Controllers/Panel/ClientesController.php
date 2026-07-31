<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Aseguradora;
use App\Models\Direccion;
use App\Http\Requests\Panel\StoreClienteRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ClientesController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        $clientes = Cliente::with('aseguradora')
            ->where('empresa_id', $empresaId)
            ->latest()
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'nombre' => trim($c->nombre . ' ' . ($c->apellido_paterno ?? '') . ' ' . ($c->apellido_materno ?? '')),
                'telefono' => $c->telefono ?? '—',
                'email' => $c->email ?? '—',
                'aseguradora' => $c->aseguradora?->nombre ?? '—',
                'poliza' => $c->numero_poliza ?? '—',
            ]);

        return Inertia::render('Panel/Clientes/Index', [
            'clientes' => $clientes,
        ]);
    }

    public function create()
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        return Inertia::render('Panel/Clientes/Create', [
            'aseguradoras' => Aseguradora::where('empresa_id', $empresaId)->get(['id', 'nombre']),
        ]);
    }

    public function store(StoreClienteRequest $request)
    {
        $user = Auth::user();

        $data = $request->validated();
        $data['empresa_id'] = $user->empresa_id;

        if (!empty($data['calle'])) {
            $dir = Direccion::create([
                'calle' => $data['calle'],
                'numero_exterior' => $data['numero_exterior'] ?? '',
                'numero_interior' => $data['numero_interior'] ?? '',
                'colonia' => $data['colonia'] ?? '',
                'codigo_postal' => $data['codigo_postal'] ?? '',
                'municipio_alcaldia' => $data['municipio_alcaldia'] ?? '',
                'ciudad' => $data['ciudad'] ?? '',
                'estado' => $data['estado'] ?? '',
                'pais' => $data['pais'] ?? 'México',
                'referencias' => $data['referencias'] ?? '',
            ]);
            $data['direccion_id'] = $dir->id;
        }

        Cliente::create($data);

        return redirect()->route('panel.clientes.index')
            ->with('success', 'Cliente creado correctamente');
    }

    public function show($id)
    {
        $cliente = Cliente::with(['aseguradora', 'direccion', 'cotizaciones.tipoServicio', 'facturas'])
            ->where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        return Inertia::render('Panel/Clientes/Show', [
            'cliente' => [
                'id' => $cliente->id,
                'nombre' => $cliente->nombre,
                'apellido_paterno' => $cliente->apellido_paterno ?? '',
                'apellido_materno' => $cliente->apellido_materno ?? '',
                'nombre_completo' => trim($cliente->nombre . ' ' . ($cliente->apellido_paterno ?? '') . ' ' . ($cliente->apellido_materno ?? '')),
                'tipo_cliente' => $cliente->tipo_cliente === 'persona_moral' ? 'Persona Moral' : 'Persona Física',
                'sexo' => $cliente->sexo,
                'curp' => $cliente->curp,
                'fecha_nacimiento' => $cliente->fecha_nacimiento?->format('d/m/Y'),
                'telefono' => $cliente->telefono ?? '—',
                'telefono_local' => $cliente->telefono_local ?? '—',
                'email' => $cliente->email ?? '—',
                'folio_ine' => $cliente->folio_ine ?? '—',
                'nacionalidad' => $cliente->nacionalidad ?? '—',
                'contacto_enlace' => $cliente->contacto_enlace ?? '—',
                'numero_poliza' => $cliente->numero_poliza ?? '—',
                'tipo_cobertura_poliza' => $cliente->tipo_cobertura_poliza ?? '—',
                'aseguradora' => $cliente->aseguradora?->nombre ?? '—',
                'direccion' => $cliente->direccion ? [
                    'calle' => $cliente->direccion->calle,
                    'numero' => $cliente->direccion->numero_exterior,
                    'colonia' => $cliente->direccion->colonia,
                    'ciudad' => $cliente->direccion->ciudad,
                    'estado' => $cliente->direccion->estado,
                ] : null,
                'cotizaciones' => $cliente->cotizaciones->map(fn ($c) => [
                    'id' => $c->id,
                    'folio' => $c->folio ?? 'COT-' . str_pad($c->id, 5, '0', STR_PAD_LEFT),
                    'tipo' => $c->tipoServicio?->nombre ?? '—',
                    'monto' => (float) ($c->costo_total ?? 0),
                    'fecha' => $c->created_at?->format('d/m/Y'),
                    'estatus' => $c->estatus,
                ]),
            ],
        ]);
    }

    public function edit($id)
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        return Inertia::render('Panel/Clientes/Create', [
            'cliente' => Cliente::with('direccion')->where('empresa_id', auth()->user()->empresa_id)->findOrFail($id),
            'aseguradoras' => Aseguradora::where('empresa_id', $empresaId)->get(['id', 'nombre']),
        ]);
    }

    public function update(StoreClienteRequest $request, $id)
    {
        $cliente = Cliente::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        $data = $request->validated();

        if (!empty($data['calle'])) {
            if ($cliente->direccion_id) {
                $cliente->direccion->update([
                    'calle' => $data['calle'],
                    'numero_exterior' => $data['numero_exterior'] ?? '',
                    'numero_interior' => $data['numero_interior'] ?? '',
                    'colonia' => $data['colonia'] ?? '',
                    'codigo_postal' => $data['codigo_postal'] ?? '',
                    'municipio_alcaldia' => $data['municipio_alcaldia'] ?? '',
                    'ciudad' => $data['ciudad'] ?? '',
                    'estado' => $data['estado'] ?? '',
                    'pais' => $data['pais'] ?? 'México',
                    'referencias' => $data['referencias'] ?? '',
                ]);
            } else {
                $dir = Direccion::create([
                    'calle' => $data['calle'],
                    'numero_exterior' => $data['numero_exterior'] ?? '',
                    'numero_interior' => $data['numero_interior'] ?? '',
                    'colonia' => $data['colonia'] ?? '',
                    'codigo_postal' => $data['codigo_postal'] ?? '',
                    'municipio_alcaldia' => $data['municipio_alcaldia'] ?? '',
                    'ciudad' => $data['ciudad'] ?? '',
                    'estado' => $data['estado'] ?? '',
                    'pais' => $data['pais'] ?? 'México',
                    'referencias' => $data['referencias'] ?? '',
                ]);
                $data['direccion_id'] = $dir->id;
            }
        }

        $cliente->update($data);

        return redirect()->route('panel.clientes.index')
            ->with('success', 'Cliente actualizado correctamente');
    }

    public function destroy($id)
    {
        $cliente = Cliente::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        $cotizacionesCount = $cliente->cotizaciones()->count();
        if ($cotizacionesCount > 0) {
            return redirect()->back()->with('error', 'No se puede eliminar el cliente porque tiene cotizaciones asociadas.');
        }

        $cliente->delete();

        return redirect()->route('panel.clientes.index')
            ->with('success', 'Cliente eliminado correctamente');
    }
}
