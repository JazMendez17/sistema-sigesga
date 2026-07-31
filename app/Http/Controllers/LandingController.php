<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class LandingController extends Controller
{
    public function index()
    {
        return Inertia::render('Landing/Index');
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