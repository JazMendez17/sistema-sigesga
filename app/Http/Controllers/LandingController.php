<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\EmpresaServicio;
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

        return Inertia::render('Landing/Index', [
            'redesSociales' => $redesSociales,
            'servicios' => $servicios,
        ]);
    }

    public function solicitar()
    {
        return Inertia::render('Landing/SolicitarServicio');
    }

    public function solicitarStore(Request $request)
    {
        $validated = $request->validate([
            'ubicacion' => 'required|string|max:255',
            'tipo_servicio' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
        ]);

        // TODO: guardar solicitud en la base de datos cuando el modelo esté listo

        return redirect()->route('solicitar')->with('success', 'Solicitud enviada correctamente.');
    }

    public function rastrear()
    {
        return Inertia::render('Landing/RastrearServicio');
    }

    public function soporte()
    {
        return Inertia::render('Landing/Soporte');
    }
}