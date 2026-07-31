<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Servicio;
use App\Models\Cotizacione;
use App\Models\Operadore;
use App\Models\CalificacionesServicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ReportesController extends Controller
{
    public function index()
    {
        return Inertia::render('Panel/Reportes/Index');
    }

    public function servicios(Request $request)
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        $from = $request->input('fecha_inicio', now()->subMonth());
        $to = $request->input('fecha_fin', now());

        $servicios = Servicio::with('cotizacion.cliente')
            ->where('empresa_id', $empresaId)
            ->whereBetween('created_at', [$from, $to])
            ->get()
            ->map(fn ($s) => [
                'folio' => 'SVC-' . str_pad($s->id, 5, '0', STR_PAD_LEFT),
                'cliente' => $s->cotizacion?->cliente?->nombre ?? '—',
                'tipo' => $s->cotizacion?->tipoServicio?->nombre ?? '—',
                'fecha' => $s->created_at?->format('d/m/Y'),
                'costo' => (float) ($s->costo_final_real ?? 0),
                'estatus' => $s->estado ?? '—',
            ]);

        return back()->with('reporte', ['type' => 'servicios', 'data' => $servicios]);
    }

    public function costos(Request $request)
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        $from = $request->input('fecha_inicio', now()->subMonth());
        $to = $request->input('fecha_fin', now());

        $servicios = Servicio::where('empresa_id', $empresaId)
            ->whereBetween('created_at', [$from, $to])
            ->get();

        $totalCostos = $servicios->sum('costo_final_real');
        $totalIngresos = $servicios->sum(fn ($s) => $s->costo_final_real ?? 0);

        return back()->with('reporte', [
            'type' => 'costos',
            'data' => [
                'total_servicios' => $servicios->count(),
                'total_ingresos' => (float) $totalIngresos,
                'total_costos' => (float) $totalCostos,
                'margen' => $totalIngresos > 0 ? round((($totalIngresos - $totalCostos) / $totalIngresos) * 100, 2) : 0,
            ],
        ]);
    }

    public function rendimiento(Request $request)
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        $operadorId = $request->input('operador_id');

        $query = Servicio::with('operador.empleado')
            ->where('empresa_id', $empresaId);

        if ($operadorId) {
            $query->where('operador_id', $operadorId);
        }

        $servicios = $query->get()->groupBy('operador_id')->map(function ($items, $opId) {
            $op = $items->first()->operador;
            return [
                'operador' => $op?->empleado?->nombre ?? '—',
                'total_servicios' => $items->count(),
                'completados' => $items->where('estado', 'finalizado')->count(),
                'calificacion_promedio' => round($items->avg('calificacionesServicio.estrellas') ?? 0, 1),
            ];
        })->values();

        return back()->with('reporte', ['type' => 'rendimiento', 'data' => $servicios]);
    }

    public function calificaciones(Request $request)
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        $calificaciones = CalificacionesServicio::with(['servicio.cotizacion.cliente', 'cliente'])
            ->whereHas('servicio', fn ($q) => $q->where('empresa_id', $empresaId))
            ->get()
            ->map(fn ($c) => [
                'cliente' => $c->cliente?->nombre ?? $c->servicio?->cotizacion?->cliente?->nombre ?? '—',
                'estrellas' => $c->estrellas ?? 0,
                'comentario' => $c->comentario ?? '—',
                'fecha' => $c->created_at?->format('d/m/Y'),
            ]);

        return back()->with('reporte', [
            'type' => 'calificaciones',
            'data' => $calificaciones,
        ]);
    }
}
