<?php

// Controlador de notificaciones operativas del sistema (borrado físico real)

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Notificacione;
use App\Support\Auditoria;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class NotificacionesController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $notificaciones = Notificacione::where('empresa_id', $user->empresa_id)
            ->where('usuario_id', $user->id)
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

        $noLeidas = Notificacione::where('empresa_id', $user->empresa_id)
            ->where('usuario_id', $user->id)
            ->where('estado', '!=', 'leido')
            ->count();

        return Inertia::render('Panel/Notificaciones/Index', [
            'notificaciones' => $notificaciones,
            'noLeidas' => $noLeidas,
        ]);
    }

    public function marcarLeida($id)
    {
        $user = Auth::user();

        $notificacion = Notificacione::where('empresa_id', $user->empresa_id)
            ->where('usuario_id', $user->id)
            ->findOrFail($id);

        $notificacion->update(['estado' => 'leido']);

        return back()->with('success', 'Notificación marcada como leída.');
    }

    public function marcarTodas()
    {
        $user = Auth::user();

        Notificacione::where('empresa_id', $user->empresa_id)
            ->where('usuario_id', $user->id)
            ->where('estado', '!=', 'leido')
            ->update(['estado' => 'leido']);

        return back()->with('success', 'Todas las notificaciones fueron marcadas como leídas.');
    }

    public function unreadCount()
    {
        $user = Auth::user();

        return response()->json([
            'count' => Notificacione::where('empresa_id', $user->empresa_id)
                ->where('usuario_id', $user->id)
                ->where('estado', '!=', 'leido')
                ->count(),
        ]);
    }

    // Borrado físico real de la notificación
    public function eliminar($id)
    {
        $notificacion = Notificacione::where('empresa_id', auth()->user()->empresa_id)
            ->where('usuario_id', auth()->id())
            ->findOrFail($id);

        Auditoria::registrar($notificacion);
        $notificacion->delete();

        return back()->with('success', 'Notificación eliminada permanentemente.');
    }
}
