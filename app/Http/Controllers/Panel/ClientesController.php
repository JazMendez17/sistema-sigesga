<?php

// Controlador de clientes (borrado físico, transacciones, fechas Y-m-d y aislamiento multi-tenant)

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\StoreClienteRequest;
use App\Models\Aseguradora;
use App\Models\Cliente;
use App\Models\Direccion;
use App\Models\Servicio;
use App\Models\Usuario;
use App\Services\ImportaClientes;
use App\Support\Auditoria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ClientesController extends Controller
{
    public function index()
    {
        $empresaId = Auth::user()->empresa_id;

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
            'machote' => ['hojas' => ['Clientes' => ImportaClientes::COLUMNAS]],
        ]);
    }

    public function create()
    {
        $empresaId = Auth::user()->empresa_id;

        return Inertia::render('Panel/Clientes/Create', [
            'aseguradoras' => Aseguradora::where('empresa_id', $empresaId)->get(['id', 'nombre']),
        ]);
    }

    public function store(StoreClienteRequest $request)
    {
        $user = Auth::user();
        $data = $request->validated();
        $data['empresa_id'] = $user->empresa_id;

        $usuarioCreado = null;

        DB::transaction(function () use ($user, &$data, &$usuarioCreado) {
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

            if (!empty($data['email'])) {
                $existente = Usuario::where('email', $data['email'])->first();
                if (!$existente) {
                    $usuarioCreado = Usuario::create([
                        'empresa_id' => $user->empresa_id,
                        'name' => trim(($data['nombre'] ?? '') . ' ' . ($data['apellido_paterno'] ?? '') . ' ' . ($data['apellido_materno'] ?? '')),
                        'email' => $data['email'],
                        'email_verified_at' => now(),
                        'password' => 'Cliente123.',
                        'debe_cambiar_password' => true,
                        'rol' => 'cliente',
                    ]);
                    $data['usuario_id'] = $usuarioCreado->id;
                } elseif (
                    $existente->empresa_id === $user->empresa_id
                    && $existente->rol === 'cliente'
                    && !Cliente::where('usuario_id', $existente->id)->exists()
                ) {
                    $data['usuario_id'] = $existente->id;
                }
            }

            Cliente::create($data);
        });

        $mensaje = 'Cliente creado correctamente.';
        if ($usuarioCreado) {
            $mensaje .= ' Usuario: ' . $usuarioCreado->email . ' | Contraseña temporal: Cliente123. (deberá cambiarla al iniciar sesión)';
        }

        return redirect()->route('panel.clientes.index')->with('success', $mensaje);
    }

    public function show($id)
    {
        $empresaId = auth()->user()->empresa_id;
        $cliente = Cliente::with(['aseguradora.convenios.convenioTarifas', 'direccion', 'cotizaciones.tipoServicio', 'cotizaciones.servicio', 'facturas'])
            ->where('empresa_id', $empresaId)
            ->findOrFail($id);

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

        $servicios = Servicio::with(['cotizacion.tipoServicio', 'operador.empleado'])
            ->where('empresa_id', $empresaId)
            ->whereHas('cotizacion', fn ($q) => $q->where('cliente_id', $cliente->id))
            ->latest()
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'folio' => 'SVC-' . str_pad((string) $s->id, 5, '0', STR_PAD_LEFT),
                'tipo' => $s->cotizacion?->tipoServicio?->nombre ?? '—',
                'operador' => $s->operador?->empleado?->nombre ?? '—',
                'monto' => (float) ($s->costo_final_real ?? $s->cotizacion?->costo_total ?? 0),
                'fecha' => $s->created_at?->format('d/m/Y'),
                'estatus' => $s->estado ?? 'asignado',
            ]);

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
                'aseguradora' => $cliente->aseguradora?->nombre ?? null,
                'aseguradora_comercial' => $cliente->aseguradora?->nombre_comercial ?? null,
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
                    'folio' => $c->folio ?? 'COT-' . str_pad((string) $c->id, 5, '0', STR_PAD_LEFT),
                    'tipo' => $c->tipoServicio?->nombre ?? '—',
                    'monto' => (float) ($c->costo_total ?? 0),
                    'fecha' => $c->created_at?->format('d/m/Y'),
                    'estatus' => $c->estatus,
                ]),
                'servicios' => $servicios,
            ],
        ]);
    }

    public function edit($id)
    {
        $empresaId = Auth::user()->empresa_id;
        $cliente = Cliente::with('direccion')->where('empresa_id', $empresaId)->findOrFail($id);
        $clienteArr = $cliente->toArray();
        if ($cliente->fecha_nacimiento) {
            $clienteArr['fecha_nacimiento'] = \Carbon\Carbon::parse($cliente->fecha_nacimiento)->format('Y-m-d');
        }

        return Inertia::render('Panel/Clientes/Create', [
            'cliente' => $clienteArr,
            'aseguradoras' => Aseguradora::where('empresa_id', $empresaId)->get(['id', 'nombre']),
        ]);
    }

    public function update(StoreClienteRequest $request, $id)
    {
        $cliente = Cliente::with(['direccion', 'usuario'])->where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);
        $data = $request->validated();

        DB::transaction(function () use ($cliente, $data) {
            if (!empty($data['calle'])) {
                $dirPayload = [
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
                ];
                if ($cliente->direccion_id && $cliente->direccion) {
                    $cliente->direccion->update($dirPayload);
                } else {
                    $dir = Direccion::create($dirPayload);
                    $data['direccion_id'] = $dir->id;
                }
            }

            $cliente->update($data);

            // Sincronizar nombre y correo con el usuario del cliente si existe y no hay conflicto
            if ($cliente->usuario) {
                $sync = [
                    'name' => trim(($cliente->nombre ?? '') . ' ' . ($cliente->apellido_paterno ?? '') . ' ' . ($cliente->apellido_materno ?? '')),
                ];
                if (!empty($cliente->email) && !Usuario::where('email', $cliente->email)->where('id', '!=', $cliente->usuario->id)->exists()) {
                    $sync['email'] = $cliente->email;
                }
                $cliente->usuario->update($sync);
            }
        });

        return redirect()->route('panel.clientes.index')->with('success', 'Cliente actualizado correctamente');
    }

    // Borrado físico real del cliente (y de su usuario de acceso si tiene rol cliente)
    public function destroy($id)
    {
        $cliente = Cliente::with(['usuario', 'direccion', 'facturas'])
            ->where('empresa_id', auth()->user()->empresa_id)
            ->findOrFail($id);

        if ($cliente->facturas->isNotEmpty()) {
            return back()->with('error', 'No se puede eliminar este cliente porque tiene facturas emitidas.');
        }

        DB::transaction(function () use ($cliente) {
            $usuario = $cliente->usuario;
            $direccion = $cliente->direccion;

            Auditoria::registrar($cliente);
            $cliente->delete();

            if ($usuario && $usuario->rol === 'cliente') {
                Auditoria::registrar($usuario);
                $usuario->delete();
            }

            $direccion?->delete();
        });

        return redirect()->route('panel.clientes.index')
            ->with('success', 'Cliente eliminado físicamente de forma correcta.');
    }
}
