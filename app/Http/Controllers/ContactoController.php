<?php

namespace App\Http\Controllers;

use App\Models\Contacto;
use App\Models\Empresa;
use App\Mail\ContactoMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ContactoController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'email' => 'required|email|max:150',
            'mensaje' => 'required|string',
        ]);

        $contacto = Contacto::create($validated);

        $empresa = Empresa::first();
        if ($empresa && $empresa->email_contacto) {
            try {
                Mail::to($empresa->email_contacto)->send(new ContactoMail($contacto));
            } catch (\Throwable $e) {
                Log::error('Error al enviar correo de contacto: ' . $e->getMessage());
            }
        }

        return response()->json(['success' => true]);
    }
}