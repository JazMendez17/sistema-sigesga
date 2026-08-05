<?php

// Controlador de notificaciones

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Notificacione;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class NotificacionesController extends Controller
{
    // Lista de notificaciones enviadas
    public function index()
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        $notificaciones = Notificacione::with('usuario')
            ->where('empresa_id', $empresaId)
            ->latest()
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'usuario' => $n->usuario?->name ?? '—',
                'mensaje' => $n->mensaje ?? '—',
                'canal' => $n->canal ?? '—',
                'estado' => $n->estado ?? 'enviado',
                'intentos' => $n->intentos_envio ?? 0,
                'fecha' => $n->created_at?->format('d/m/Y H:i'),
            ]);

        return Inertia::render('Panel/Notificaciones/Index', [
            'notificaciones' => $notificaciones,
        ]);
    }

    // Reenviar notificación fallida
    public function reenviar($id)
    {
        $notificacion = Notificacione::findOrFail($id);

        $notificacion->update([
            'estado' => 'enviado',
            'intentos_envio' => ($notificacion->intentos_envio ?? 0) + 1,
        ]);

        return back()->with('success', 'Notificación reenviada correctamente');
    }
}
