<?php

namespace App\Http\Controllers;

use App\Models\Contacto;
use Illuminate\Http\Request;

class ContactoController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'email' => 'required|email|max:150',
            'mensaje' => 'required|string',
        ]);

        Contacto::create($validated);

        return redirect()->back()->with('success', 'Mensaje enviado correctamente. Nos pondremos en contacto contigo pronto.');
    }
}