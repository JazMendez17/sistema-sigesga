<?php

// Controlador de clientes

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Aseguradora;
use App\Models\Direccion;
use App\Models\Usuario;
use App\Http\Requests\Panel\StoreClienteRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ClientesController extends Controller
{
    // Lista de clientes
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

    // Formulario para crear cliente
    public function create()
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        return Inertia::render('Panel/Clientes/Create', [
            'aseguradoras' => Aseguradora::where('empresa_id', $empresaId)->get(['id', 'nombre']),
        ]);
    }

    // Guardar cliente en base de datos
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

        // Crear usuario para el cliente si tiene email
        $usuarioCreado = null;
        if (!empty($data['email'])) {
            $usuario = Usuario::firstOrCreate(
                ['email' => $data['email']],
                [
                    'empresa_id' => $user->empresa_id,
                    'name' => trim(($data['nombre'] ?? '') . ' ' . ($data['apellido_paterno'] ?? '') . ' ' . ($data['apellido_materno'] ?? '')),
                    'password' => 'Cliente123.',
                    'debe_cambiar_password' => true,
                    'rol' => 'cliente',
                ]
            );
            $data['usuario_id'] = $usuario->id;
            if ($usuario->wasRecentlyCreated) $usuarioCreado = $usuario;
        }

        Cliente::create($data);

        $mensaje = 'Cliente creado correctamente.';
        if ($usuarioCreado) {
            $mensaje .= ' Usuario: ' . $usuarioCreado->email . ' | Contraseña: Cliente123.';
        }

        return redirect()->route('panel.clientes.index')
            ->with('success', $mensaje);
    }

    // Ver detalle de cliente
    public function show($id)
    {
        $cliente = Cliente::with(['aseguradora.convenios.convenioTarifas', 'direccion', 'cotizaciones.tipoServicio', 'facturas'])
            ->where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        $convenioInfo = null;
        if ($cliente->aseguradora) {
            $convenio = $cliente->aseguradora->convenios->first();
            if ($convenio) {
                $convenioInfo = [
                    'nombre' => $convenio->nombre_convenio_poliza ?? '—',
                    'codigo' => $convenio->codigo_convenio ?? '—',
                    'cubre_casetas' => (bool) ($convenio->cubre_casetas_peaje ?? false),
                    'dias_credito' => $convenio->dias_credito ?? '—',
                    'tarifas' => $convenio->convenioTarifas->map(fn ($t) => [
                        'servicio' => $t->servicio ?? '—',
                        'banderazo' => (float) $t->banderazo,
                        'km_incluidos' => (int) $t->km_incluidos,
                        'costo_km_extra' => (float) $t->costo_km_extra,
                    ]),
                ];
            }
        }

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
                'fecha_nacimiento' => $cliente->fecha_nacimiento ? \Carbon\Carbon::parse($cliente->fecha_nacimiento)->format('d/m/Y') : null,
                'telefono' => $cliente->telefono ?? '—',
                'telefono_local' => $cliente->telefono_local ?? '—',
                'email' => $cliente->email ?? '—',
                'folio_ine' => $cliente->folio_ine ?? '—',
                'nacionalidad' => $cliente->nacionalidad ?? '—',
                'contacto_enlace' => $cliente->contacto_enlace ?? '—',
                'numero_poliza' => $cliente->numero_poliza ?? '—',
                'tipo_cobertura_poliza' => $cliente->tipo_cobertura_poliza ?? '—',
                'aseguradora' => $cliente->aseguradora?->nombre ?? '—',
                'aseguradora_comercial' => $cliente->aseguradora?->nombre_comercial ?? '—',
                'convenio' => $convenioInfo,
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

    // Formulario para editar cliente
    public function edit($id)
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        return Inertia::render('Panel/Clientes/Create', [
            'cliente' => Cliente::with('direccion')->where('empresa_id', auth()->user()->empresa_id)->findOrFail($id),
            'aseguradoras' => Aseguradora::where('empresa_id', $empresaId)->get(['id', 'nombre']),
        ]);
    }

    // Actualizar datos del cliente
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

    // Eliminar cliente
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
