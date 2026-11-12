<?php

// Controlador de autorizaciones de cancelación

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\AutorizacionesCancelacione;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AutorizacionesCancelacionController extends Controller
{
    // Lista de autorizaciones de cancelación
    public function index()
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        $autorizaciones = AutorizacionesCancelacione::with([
            'servicio.cotizacion.cliente',
            'usuarioSolicitante',
            'usuarioResolutor',
        ])->whereHas('servicio', fn ($q) => $q->where('empresa_id', $empresaId))
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'folio_servicio' => $a->servicio_id ? 'SVC-' . str_pad($a->servicio_id, 5, '0', STR_PAD_LEFT) : '—',
                'solicitante' => $a->usuarioSolicitante?->name ?? '—',
                'motivo' => $a->motivo_cancelacion ?? '—',
                'tipo_incidencia' => $a->tipo_incidencia ?? '—',
                'fecha' => $a->fecha_solicitud ?? $a->created_at?->format('d/m/Y') ?? '—',
                'estatus' => $a->estatus ?? 'pendiente',
            ]);

        return Inertia::render('Panel/AutorizacionesCancelacion/Index', [
            'autorizaciones' => $autorizaciones,
        ]);
    }

    // Aprobar solicitud de cancelación
    public function aprobar($id)
    {
        DB::transaction(function () use ($id) {
            $auth = AutorizacionesCancelacione::where('estatus', 'pendiente')
                ->whereHas('servicio', fn ($q) => $q->where('empresa_id', auth()->user()->empresa_id))
                ->lockForUpdate()->findOrFail($id);
            $servicio = $auth->servicio()->lockForUpdate()->firstOrFail();
            $auth->update([
                'estatus' => Auth::user()->rol === 'admin' ? 'cancelado_por_admin' : 'cancelado_por_cotizador',
                'usuario_resolutor_id' => Auth::id(),
                'fecha_resolucion' => now(),
            ]);
            $servicio->update(['estado' => 'cancelado']);
            if ($servicio->operador_id) {
                \App\Models\Operadore::where('id', $servicio->operador_id)
                    ->where('empresa_id', $servicio->empresa_id)->update(['disponible' => true]);
            }
        });

        return back()->with('success', 'Autorización aprobada');
    }

    // Rechazar solicitud de cancelación
    public function rechazar($id)
    {
        $auth = AutorizacionesCancelacione::where('estatus', 'pendiente')
            ->whereHas('servicio', fn ($q) => $q->where('empresa_id', auth()->user()->empresa_id))->findOrFail($id);
        $auth->update([
            'estatus' => 'rechazada',
            'usuario_resolutor_id' => Auth::id(),
            'fecha_resolucion' => now(),
        ]);

        if ($auth->servicio && $auth->servicio->estado === 'solicitud_cancelacion') {
            $auth->servicio->update(['estado' => 'asignado']);
        }

        return back()->with('success', 'Autorización rechazada');
    }
}
