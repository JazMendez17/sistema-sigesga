<?php

// Controlador de autorizaciones de cancelación

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\AutorizacionesCancelacione;
use App\Models\Operadore;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AutorizacionesCancelacionController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        $query = AutorizacionesCancelacione::with([
            'servicio.cotizacion.cliente',
            'usuarioSolicitante',
            'usuarioResolutor',
        ])->whereHas('servicio', fn ($q) => $q->where('empresa_id', $empresaId));

        if ($user->rol === 'operador') {
            $query->where('usuario_solicitante_id', $user->id);
        }

        $autorizaciones = $query->orderByDesc('id')
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'folio_servicio' => $a->servicio_id ? 'SVC-' . str_pad((string) $a->servicio_id, 5, '0', STR_PAD_LEFT) : '—',
                'solicitante' => $a->usuarioSolicitante?->name ?? '—',
                'motivo' => $a->motivo_cancelacion ?? '—',
                'tipo_incidencia' => $a->tipo_incidencia ?? '—',
                'fecha' => $a->fecha_solicitud ? $a->fecha_solicitud->format('d/m/Y H:i') : '—',
                'estatus' => in_array($a->estatus, ['aprobada', 'cancelado_por_admin', 'cancelado_por_cotizador'], true)
                    ? 'aprobada'
                    : ($a->estatus ?? 'pendiente'),
            ]);

        return Inertia::render('Panel/AutorizacionesCancelacion/Index', [
            'autorizaciones' => $autorizaciones,
            'puedeResolver' => in_array($user->rol, ['superadmin', 'admin', 'cotizador'], true),
        ]);
    }

    public function aprobar($id)
    {
        DB::transaction(function () use ($id) {
            $auth = AutorizacionesCancelacione::where('estatus', 'pendiente')
                ->whereHas('servicio', fn ($q) => $q->where('empresa_id', auth()->user()->empresa_id))
                ->lockForUpdate()->findOrFail($id);

            $servicio = $auth->servicio()->lockForUpdate()->firstOrFail();

            $auth->update([
                'estatus' => 'aprobada',
                'usuario_resolutor_id' => Auth::id(),
                'fecha_resolucion' => now(),
            ]);

            $servicio->update(['estado' => 'cancelado']);

            if ($servicio->operador_id) {
                Operadore::where('id', $servicio->operador_id)
                    ->where('empresa_id', $servicio->empresa_id)
                    ->update(['disponible' => true]);
            }
        });

        return back()->with('success', 'Autorización aprobada');
    }

    public function rechazar($id)
    {
        DB::transaction(function () use ($id) {
            $auth = AutorizacionesCancelacione::where('estatus', 'pendiente')
                ->whereHas('servicio', fn ($q) => $q->where('empresa_id', auth()->user()->empresa_id))
                ->lockForUpdate()->findOrFail($id);

            $auth->update([
                'estatus' => 'rechazada',
                'usuario_resolutor_id' => Auth::id(),
                'fecha_resolucion' => now(),
            ]);

            $servicio = $auth->servicio()->lockForUpdate()->first();
            if ($servicio && $servicio->estado === 'solicitud_cancelacion') {
                // Restaura el estado previo sin disparar falsas notificaciones de nueva asignación
                $servicio->estado = $auth->estado_previo ?: 'asignado';
                $servicio->saveQuietly();
            }
        });

        return back()->with('success', 'Autorización rechazada');
    }
}
