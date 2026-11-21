<?php

// Controlador de reportes

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\CalificacionesServicio;
use App\Models\Servicio;
use App\Models\UnidadMantenimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ReportesController extends Controller
{
    public function index()
    {
        return Inertia::render('Panel/Reportes/Index');
    }

    protected function rangoFechas(Request $request): array
    {
        $validated = $request->validate([
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
        ], [
            'fecha_fin.after_or_equal' => 'La fecha de fin no puede ser anterior a la fecha de inicio.',
        ]);

        $from = !empty($validated['fecha_inicio'])
            ? \Carbon\Carbon::parse($validated['fecha_inicio'])->startOfDay()
            : now()->subMonth()->startOfDay();

        $to = !empty($validated['fecha_fin'])
            ? \Carbon\Carbon::parse($validated['fecha_fin'])->endOfDay()
            : now()->endOfDay();

        return [$from, $to];
    }

    public function servicios(Request $request)
    {
        $empresaId = Auth::user()->empresa_id;
        [$from, $to] = $this->rangoFechas($request);

        $servicios = Servicio::with(['cotizacion.cliente', 'cotizacion.tipoServicio'])
            ->where('empresa_id', $empresaId)
            ->whereBetween('created_at', [$from, $to])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($s) => [
                'folio' => 'SVC-' . str_pad((string) $s->id, 5, '0', STR_PAD_LEFT),
                'cliente' => $s->cotizacion?->cliente?->nombre ?? '—',
                'tipo' => $s->cotizacion?->tipoServicio?->nombre ?? '—',
                'fecha' => $s->created_at?->format('d/m/Y'),
                'costo' => (float) ($s->costo_final_real ?? $s->cotizacion?->costo_total ?? 0),
                'estatus' => $s->estado ?? '—',
            ]);

        return back()->with('reporte', ['type' => 'servicios', 'data' => $servicios]);
    }

    public function costos(Request $request)
    {
        $empresaId = Auth::user()->empresa_id;
        [$from, $to] = $this->rangoFechas($request);

        $servicios = Servicio::with('cotizacion')
            ->where('empresa_id', $empresaId)
            ->whereBetween('created_at', [$from, $to])
            ->get();

        // Ingresos reales de servicios finalizados (no se resta cargo_zona_especial porque es un cobro al cliente)
        $finalizados = $servicios->where('estado', 'finalizado');
        $totalIngresos = $finalizados->sum(fn ($s) => (float) ($s->costo_final_real ?? $s->cotizacion?->costo_total ?? 0));

        // Costos operativos registrados en mantenimientos de flotilla dentro del periodo
        $totalCostos = (float) UnidadMantenimiento::where('empresa_id', $empresaId)
            ->whereBetween('fecha', [$from->toDateString(), $to->toDateString()])
            ->sum('costo');

        return back()->with('reporte', [
            'type' => 'costos',
            'data' => [
                'total_servicios' => $servicios->count(),
                'total_ingresos' => round($totalIngresos, 2),
                'total_costos' => round($totalCostos, 2),
                'margen' => round($totalIngresos - $totalCostos, 2),
            ],
        ]);
    }

    public function rendimiento(Request $request)
    {
        $empresaId = Auth::user()->empresa_id;
        $operador = trim((string) $request->input('operador', ''));

        $query = Servicio::with(['operador.empleado', 'calificacionesServicio'])
            ->where('empresa_id', $empresaId);

        if ($request->filled('fecha_inicio') || $request->filled('fecha_fin')) {
            [$from, $to] = $this->rangoFechas($request);
            $query->whereBetween('created_at', [$from, $to]);
        }

        if ($operador !== '') {
            $like = '%' . addcslashes($operador, '%_\\') . '%';
            $query->whereHas('operador.empleado', function ($q) use ($like) {
                $q->where('nombre', 'like', $like)
                    ->orWhere('apellido_paterno', 'like', $like)
                    ->orWhere('apellido_materno', 'like', $like);
            });
        }

        $servicios = $query->get()->groupBy('operador_id')->map(function ($items) {
            $emp = $items->first()->operador?->empleado;
            $nombreCompleto = $emp ? trim("{$emp->nombre} {$emp->apellido_paterno} {$emp->apellido_materno}") : '—';
            $calificaciones = $items->pluck('calificacionesServicio')->filter();

            return [
                'operador' => $nombreCompleto,
                'total_servicios' => $items->count(),
                'completados' => $items->where('estado', 'finalizado')->count(),
                'calificacion_promedio' => $calificaciones->count()
                    ? round($calificaciones->avg('estrellas'), 1)
                    : 0,
            ];
        })->values();

        return back()->with('reporte', ['type' => 'rendimiento', 'data' => $servicios]);
    }

    public function calificaciones(Request $request)
    {
        $empresaId = Auth::user()->empresa_id;
        [$from, $to] = $this->rangoFechas($request);

        $calificaciones = CalificacionesServicio::with(['servicio.cotizacion.cliente', 'cliente'])
            ->whereHas('servicio', fn ($q) => $q->where('empresa_id', $empresaId))
            ->whereBetween('created_at', [$from, $to])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($c) => [
                'cliente' => $c->cliente?->nombre ?? $c->servicio?->cotizacion?->cliente?->nombre ?? '—',
                'estrellas' => (int) ($c->estrellas ?? 0),
                'comentario' => $c->comentario ?? '—',
                'fecha' => $c->created_at?->format('d/m/Y') ?? '—',
            ]);

        return back()->with('reporte', [
            'type' => 'calificaciones',
            'data' => $calificaciones,
        ]);
    }
}
