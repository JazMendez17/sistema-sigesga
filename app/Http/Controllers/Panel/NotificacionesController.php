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

        $query = Notificacione::where('empresa_id', $user->empresa_id);

        // Admins y cotizadores ven todas las notificaciones de la empresa
        // Operadores y clientes solo ven las suyas
        if (!in_array($user->rol, ['admin', 'cotizador'])) {
            $query->where('usuario_id', $user->id);
        }

        $notificaciones = $query->latest()
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
            ->when(!in_array($user->rol, ['admin', 'cotizador']), fn($q) => $q->where('usuario_id', $user->id))
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
        $user = Auth::user();

        $query = Notificacione::where('empresa_id', $user->empresa_id);
        if (!in_array($user->rol, ['admin', 'cotizador'])) {
            $query->where('usuario_id', $user->id);
        }

        $notificacion = $query->findOrFail($id);
        $notificacion->update(['estado' => 'leido']);

        return back()->with('success', 'Notificación marcada como leída.');
    }

    // Marcar todas las notificaciones visibles como leídas
    public function marcarTodas()
    {
        $user = Auth::user();

        $query = Notificacione::where('empresa_id', $user->empresa_id);
        if (!in_array($user->rol, ['admin', 'cotizador'])) {
            $query->where('usuario_id', $user->id);
        }

        $query->where('estado', '!=', 'leido')->update(['estado' => 'leido']);

        return back()->with('success', 'Todas las notificaciones fueron marcadas como leídas.');
    }

    // Endpoint para polling: devuelve conteo de no leídas
    public function unreadCount()
    {
        $user = Auth::user();

        return response()->json([
            'count' => Notificacione::where('empresa_id', $user->empresa_id)
                ->when(!in_array($user->rol, ['admin', 'cotizador']), fn($q) => $q->where('usuario_id', $user->id))
                ->where('estado', '!=', 'leido')
                ->count(),
        ]);
    }
}
