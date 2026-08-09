<?php

// Controlador de notificaciones operativas del sistema

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Notificacione;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class NotificacionesController extends Controller
{
    // Lista de notificaciones del usuario autenticado
    public function index()
    {
        $user = Auth::user();

        $notificaciones = Notificacione::where('usuario_id', $user->id)
            ->latest()
            ->paginate(15)
            ->through(fn ($n) => [
                'id' => $n->id,
                'mensaje' => $n->mensaje ?? '—',
                'canal' => $n->canal ?? '—',
                'estado' => $n->estado ?? 'pendiente',
                'fecha' => $n->created_at?->format('d/m/Y H:i'),
                'hora' => $n->created_at?->format('H:i'),
            ]);

        $noLeidas = Notificacione::where('usuario_id', $user->id)
            ->where('estado', '!=', 'leido')
            ->count();

        return Inertia::render('Panel/Notificaciones/Index', [
            'notificaciones' => $notificaciones,
            'noLeidas' => $noLeidas,
        ]);
    }

    // Marcar una notificación como leída
    public function marcarLeida($id)
    {
        $notificacion = Notificacione::where('usuario_id', auth()->id())->findOrFail($id);
        $notificacion->update(['estado' => 'leido']);

        return back()->with('success', 'Notificación marcada como leída.');
    }
}
