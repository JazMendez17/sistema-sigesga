<?php

// Controlador de reportes

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Servicio;
use App\Models\CalificacionesServicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ReportesController extends Controller
{
    // Página principal de reportes
    public function index()
    {
        return Inertia::render('Panel/Reportes/Index');
    }

    // Rango de fechas validado con valores por defecto
    protected function rangoFechas(Request $request): array
    {
        $validated = $request->validate([
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date'],
        ]);

        $from = !empty($validated['fecha_inicio'])
            ? \Carbon\Carbon::parse($validated['fecha_inicio'])->startOfDay()
            : now()->subMonth()->startOfDay();

        $to = !empty($validated['fecha_fin'])
            ? \Carbon\Carbon::parse($validated['fecha_fin'])->endOfDay()
            : now()->endOfDay();

        return [$from, $to];
    }

    // Reporte de servicios por periodo
    public function servicios(Request $request)
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;
        [$from, $to] = $this->rangoFechas($request);

        $servicios = Servicio::with('cotizacion.cliente')
            ->where('empresa_id', $empresaId)
            ->whereBetween('created_at', [$from, $to])
            ->orderByDesc('created_at')
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

    // Reporte de costos e ingresos
    public function costos(Request $request)
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;
        [$from, $to] = $this->rangoFechas($request);

        $servicios = Servicio::where('empresa_id', $empresaId)
            ->whereBetween('created_at', [$from, $to])
            ->get();

        // Solo los servicios no cancelados generan ingreso
        $ingresables = $servicios->where('estado', '!=', 'cancelado');

        $totalIngresos = $ingresables->sum(fn ($s) => (float) ($s->costo_final_real ?? 0));
        $totalCostos = (float) $servicios->sum('cargo_zona_especial');

        return back()->with('reporte', [
            'type' => 'costos',
            'data' => [
                'total_servicios' => $servicios->count(),
                'total_ingresos' => $totalIngresos,
                'total_costos' => $totalCostos,
                'margen' => $totalIngresos - $totalCostos,
            ],
        ]);
    }

    // Reporte de rendimiento por operador
    public function rendimiento(Request $request)
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        $operador = trim((string) $request->input('operador', ''));

        $query = Servicio::with(['operador.empleado', 'calificacionesServicio'])
            ->where('empresa_id', $empresaId);

        if (!empty($operador)) {
            $query->whereHas('operador.empleado', function ($q) use ($operador) {
                $q->where('nombre', 'like', "%{$operador}%");
            });
        }

        $servicios = $query->get()->groupBy('operador_id')->map(function ($items, $opId) {
            $op = $items->first()->operador;
            $calificaciones = $items->pluck('calificacionesServicio')->filter();
            return [
                'operador' => $op?->empleado?->nombre ?? '—',
                'total_servicios' => $items->count(),
                'completados' => $items->where('estado', 'finalizado')->count(),
                'calificacion_promedio' => $calificaciones->count()
                    ? round($calificaciones->avg('estrellas'), 1)
                    : 0,
            ];
        })->values();

        return back()->with('reporte', ['type' => 'rendimiento', 'data' => $servicios]);
    }

    // Reporte de calificaciones de servicio
    public function calificaciones(Request $request)
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;
        [$from, $to] = $this->rangoFechas($request);

        $calificaciones = CalificacionesServicio::with(['servicio.cotizacion.cliente', 'cliente'])
            ->whereHas('servicio', fn ($q) => $q->where('empresa_id', $empresaId))
            ->whereBetween('created_at', [$from, $to])
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