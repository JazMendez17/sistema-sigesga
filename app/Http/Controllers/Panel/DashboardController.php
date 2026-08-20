<?php

// Controlador del dashboard principal

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Cotizacione;
use App\Models\Servicio;
use App\Models\Cliente;
use App\Models\Operadore;
use App\Models\Factura;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    // Mostrar dashboard con KPIs según rol del usuario
    public function index()
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        $data = ['role' => $user->rol];

        if (in_array($user->rol, ['admin', 'cotizador'])) {
            $data['kpis'] = [
                ['title' => 'Cotizaciones Pendientes', 'value' => (string) Cotizacione::where('empresa_id', $empresaId)->where('estatus', 'pendiente')->count(), 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', 'color' => '#4F46E5'],
                ['title' => 'Servicios Activos', 'value' => (string) Servicio::where('empresa_id', $empresaId)->whereIn('estado', ['asignado', 'inicio_servicio', 'en_sitio_origen', 'salida_destino', 'en_destino'])->count(), 'icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4', 'color' => '#059669'],
                ['title' => 'Operadores Disponibles', 'value' => (string) Operadore::where('empresa_id', $empresaId)->where('disponible', true)->count(), 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z', 'color' => '#D97706'],
                ['title' => 'Ingresos del Mes', 'value' => '$' . number_format(Servicio::where('empresa_id', $empresaId)
                    ->whereYear('created_at', now()->year)
                    ->whereMonth('created_at', now()->month)
                    ->where('estado', '!=', 'cancelado')
                    ->sum('costo_final_real'), 2), 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => '#7C3AED'],
            ];

            // Registros de cotizaciones por periodo: día, semana y mes
            $diasEs = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
            $mesesEs = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

            $registrosPorDia = collect(range(0, 6))->map(function ($i) use ($empresaId, $diasEs) {
                $d = now()->startOfWeek()->addDays($i);

                return [
                    'label' => $diasEs[$d->dayOfWeek],
                    'sub' => $d->format('d/m'),
                    'count' => $d->gt(now()) ? 0 : Cotizacione::where('empresa_id', $empresaId)
                        ->whereDate('created_at', $d)
                        ->count(),
                ];
            });
            $maxCount = max($registrosPorDia->max('count'), 1);
            $data['registrosPorDia'] = $registrosPorDia->map(fn ($r) => [
                'label' => $r['label'],
                'sub' => $r['sub'],
                'count' => $r['count'],
                'height' => $r['count'] > 0 ? round(($r['count'] / $maxCount) * 100) : 0,
            ])->values();

            $registrosPorSemana = collect(range(3, 0))->map(function ($i) use ($empresaId) {
                $inicio = now()->subWeeks($i)->startOfWeek();
                $fin = now()->subWeeks($i)->endOfWeek();
                $count = Cotizacione::where('empresa_id', $empresaId)
                    ->whereBetween('created_at', [$inicio, $fin])
                    ->count();

                return [
                    'label' => 'Sem ' . (4 - $i),
                    'sub' => $inicio->format('d/m') . ' - ' . $fin->format('d/m'),
                    'count' => $count,
                ];
            });
            $maxCount = max($registrosPorSemana->max('count'), 1);
            $data['registrosPorSemana'] = $registrosPorSemana->map(fn ($r) => [
                'label' => $r['label'],
                'sub' => $r['sub'],
                'count' => $r['count'],
                'height' => $r['count'] > 0 ? round(($r['count'] / $maxCount) * 100) : 0,
            ])->values();

            $registrosPorMes = collect(range(5, 0))->map(function ($i) use ($empresaId, $mesesEs) {
                $inicio = now()->subMonths($i)->startOfMonth();
                $fin = now()->subMonths($i)->endOfMonth();
                $count = Cotizacione::where('empresa_id', $empresaId)
                    ->whereBetween('created_at', [$inicio, $fin])
                    ->count();

                return ['label' => $mesesEs[$inicio->month - 1], 'sub' => $inicio->format('m/Y'), 'count' => $count];
            });
            $maxCount = max($registrosPorMes->max('count'), 1);
            $data['registrosPorMes'] = $registrosPorMes->map(fn ($r) => [
                'label' => $r['label'],
                'sub' => $r['sub'],
                'count' => $r['count'],
                'height' => $r['count'] > 0 ? round(($r['count'] / $maxCount) * 100) : 0,
            ])->values();

            // Facturas terminadas (vigentes) por periodo: día, semana y mes (admin y cotizador)
            if (in_array($user->rol, ['admin', 'cotizador'])) {
                $facturasPorDia = collect(range(0, 6))->map(function ($i) use ($empresaId, $diasEs) {
                    $d = now()->startOfWeek()->addDays($i);
                    $stats = $d->gt(now())
                        ? ['count' => 0, 'monto' => 0]
                        : Factura::where('empresa_id', $empresaId)
                            ->where('estatus', 'vigente')
                            ->whereDate('created_at', $d)
                            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total), 0) as monto')
                            ->first()
                            ->toArray();

                    return [
                        'label' => $diasEs[$d->dayOfWeek],
                        'sub' => $d->format('d/m'),
                        'count' => (int) $stats['count'],
                        'monto' => (float) $stats['monto'],
                    ];
                });
                $maxCount = max($facturasPorDia->max('count'), 1);
                $data['facturasPorDia'] = $facturasPorDia->map(fn ($r) => $r + [
                    'height' => $r['count'] > 0 ? round(($r['count'] / $maxCount) * 100) : 0,
                ])->values();

                $facturasPorSemana = collect(range(3, 0))->map(function ($i) use ($empresaId) {
                    $inicio = now()->subWeeks($i)->startOfWeek();
                    $fin = now()->subWeeks($i)->endOfWeek();
                    $stats = Factura::where('empresa_id', $empresaId)
                        ->where('estatus', 'vigente')
                        ->whereBetween('created_at', [$inicio, $fin])
                        ->selectRaw('COUNT(*) as count, COALESCE(SUM(total), 0) as monto')
                        ->first()
                        ->toArray();

                    return [
                        'label' => 'Sem ' . (4 - $i),
                        'sub' => $inicio->format('d/m') . ' - ' . $fin->format('d/m'),
                        'count' => (int) $stats['count'],
                        'monto' => (float) $stats['monto'],
                    ];
                });
                $maxCount = max($facturasPorSemana->max('count'), 1);
                $data['facturasPorSemana'] = $facturasPorSemana->map(fn ($r) => $r + [
                    'height' => $r['count'] > 0 ? round(($r['count'] / $maxCount) * 100) : 0,
                ])->values();

                $facturasPorMes = collect(range(5, 0))->map(function ($i) use ($empresaId, $mesesEs) {
                    $inicio = now()->subMonths($i)->startOfMonth();
                    $fin = now()->subMonths($i)->endOfMonth();
                    $stats = Factura::where('empresa_id', $empresaId)
                        ->where('estatus', 'vigente')
                        ->whereBetween('created_at', [$inicio, $fin])
                        ->selectRaw('COUNT(*) as count, COALESCE(SUM(total), 0) as monto')
                        ->first()
                        ->toArray();

                    return [
                        'label' => $mesesEs[$inicio->month - 1],
                        'sub' => $inicio->format('m/Y'),
                        'count' => (int) $stats['count'],
                        'monto' => (float) $stats['monto'],
                    ];
                });
                $maxCount = max($facturasPorMes->max('count'), 1);
                $data['facturasPorMes'] = $facturasPorMes->map(fn ($r) => $r + [
                    'height' => $r['count'] > 0 ? round(($r['count'] / $maxCount) * 100) : 0,
                ])->values();
            }

            $totalServicios = Servicio::where('empresa_id', $empresaId)
                ->whereNotIn('estado', ['cancelado', 'solicitud_cancelacion'])
                ->count();
            $serviciosCompletados = Servicio::where('empresa_id', $empresaId)->where('estado', 'finalizado')->count();
            $data['eficiencia'] = $totalServicios > 0 ? round(($serviciosCompletados / $totalServicios) * 100) : 0;

            $data['resumenOperacion'] = [
                'asignados' => Servicio::where('empresa_id', $empresaId)->where('estado', 'asignado')->count(),
                'enTransito' => Servicio::where('empresa_id', $empresaId)->whereIn('estado', ['inicio_servicio', 'en_sitio_origen', 'salida_destino', 'en_destino'])->count(),
                'finalizados' => Servicio::where('empresa_id', $empresaId)->where('estado', 'finalizado')->count(),
            ];

            // Modo demostración: datos de ejemplo para visualizar las gráficas
            if (env('CHARTS_DEMO_DATA', false)) {
                $demo = $this->registrosDemo();
                $data['registrosPorDia'] = $demo['dia'];
                $data['registrosPorSemana'] = $demo['semana'];
                $data['registrosPorMes'] = $demo['mes'];
                $data['eficiencia'] = 87;
                $data['resumenOperacion'] = ['asignados' => 4, 'enTransito' => 6, 'finalizados' => 18];
                $data['demoData'] = true;

                if (in_array($user->rol, ['admin', 'cotizador'])) {
                    $demoFacturas = $this->facturasDemo();
                    $data['facturasPorDia'] = $demoFacturas['dia'];
                    $data['facturasPorSemana'] = $demoFacturas['semana'];
                    $data['facturasPorMes'] = $demoFacturas['mes'];
                }
            }

            $data['recentActivity'] = collect();
            $cotizaciones = Cotizacione::with('cliente', 'tipoServicio')->where('empresa_id', $empresaId)->latest()->take(3)->get();
            $servicios = Servicio::with('cotizacion.cliente', 'cotizacion.tipoServicio')->where('empresa_id', $empresaId)->latest()->take(3)->get();

            $items = collect();
            foreach ($cotizaciones as $c) {
                $items->push([
                    'id' => $c->id,
                    'type' => 'cotizacion',
                    'title' => 'Cotización #' . ($c->folio ?? str_pad($c->id, 5, '0', STR_PAD_LEFT)),
                    'description' => ($c->cliente?->nombre ?? '—') . ' — ' . ($c->tipoServicio?->nombre ?? '—'),
                    'time' => $c->created_at?->locale('es')->diffForHumans() ?? '—',
                    'status' => $c->estatus ?? 'pendiente',
                ]);
            }
            foreach ($servicios as $s) {
                $items->push([
                    'id' => $s->id,
                    'type' => 'servicio',
                    'title' => 'Servicio #' . str_pad($s->id, 5, '0', STR_PAD_LEFT),
                    'description' => ($s->cotizacion?->cliente?->nombre ?? '—') . ' — ' . ($s->cotizacion?->tipoServicio?->nombre ?? '—'),
                    'time' => $s->created_at?->locale('es')->diffForHumans() ?? '—',
                    'status' => $s->estado ?? 'pendiente',
                ]);
            }
            $data['recentActivity'] = $items->sortByDesc('created_at')->take(5)->values()
                ->map(function ($item) {
                    $item['key'] = $item['type'] . '-' . $item['id'];
                    return $item;
                });
        }

        if ($user->rol === 'operador') {
            $operador = Operadore::where('empresa_id', $empresaId)
                ->where('empleado_id', $user->empleado_id)
                ->first();

            $data['disponible'] = $operador?->disponible ?? null;
            $data['tieneOperador'] = (bool) $operador;

            $queryServiciosHoy = Servicio::with('cotizacion.cliente')
                ->where('empresa_id', $empresaId)
                ->whereDate('created_at', today())
                ->where('operador_id', $operador?->id);

            $data['serviciosHoy'] = $queryServiciosHoy->orderByDesc('created_at')->get()
                ->map(fn ($s) => [
                    'id' => $s->id,
                    'cliente' => $s->cotizacion?->cliente?->nombre ?? '—',
                    'ruta' => 'SVC-' . str_pad($s->id, 5, '0', STR_PAD_LEFT) . ' • ' . ($s->cotizacion?->origen_direccion ?? '—') . ' → ' . ($s->cotizacion?->destino_direccion ?? '—'),
                    'horario' => $s->created_at?->format('H:i') . ' - ' . $s->updated_at?->format('H:i'),
                    'status' => $s->estado ?? 'asignado',
                ]);

            $siguiente = $queryServiciosHoy
                ->whereNotIn('estado', ['finalizado', 'cancelado', 'solicitud_cancelacion'])
                ->orderBy('created_at')
                ->first();

            $data['siguienteServicio'] = $siguiente ? [
                'cliente' => $siguiente->cotizacion?->cliente?->nombre ?? '—',
                'destino' => $siguiente->cotizacion?->destino_direccion ?? '—',
                'inicio' => $siguiente->created_at?->format('H:i'),
                'folio' => 'SVC-' . str_pad($siguiente->id, 5, '0', STR_PAD_LEFT),
            ] : null;
        }

        if ($user->rol === 'cliente') {
            $cliente = Cliente::where('usuario_id', $user->id)->first();
            $clienteId = $cliente?->id;

            $data['historialCliente'] = Servicio::with('cotizacion', 'calificacionesServicio')
                ->whereHas('cotizacion', fn ($q) => $q->where('cliente_id', $clienteId))
                ->latest()
                ->take(5)
                ->get()
                ->map(fn ($s) => [
                    'id' => $s->id,
                    'servicio' => $s->cotizacion?->tipoServicio?->nombre ?? 'Transporte',
                    'fecha' => $s->created_at?->format('d M Y'),
                    'status' => $s->estado ?? 'finalizado',
                    'monto' => '$' . number_format($s->costo_final_real ?? $s->cotizacion?->costo_total ?? 0, 2),
                    'evaluado' => (bool) $s->calificacionesServicio,
                ]);

            $data['cotizacionesCliente'] = Cotizacione::where('cliente_id', $clienteId)
                ->latest()->take(5)->get()
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'folio' => $c->folio ?? 'COT-' . str_pad($c->id, 5, '0', STR_PAD_LEFT),
                    'tipo' => $c->tipoServicio?->nombre ?? '—',
                    'estatus' => $c->estatus,
                    'fecha' => $c->created_at?->format('d/m/Y'),
                    'total' => '$' . number_format($c->costo_total ?? 0, 2),
                ]);

            $data['facturasCliente'] = \App\Models\Factura::where('cliente_id', $clienteId)
                ->latest()->take(5)->get()
                ->map(fn ($f) => [
                    'id' => $f->id,
                    'folio' => $f->folio_factura,
                    'total' => '$' . number_format($f->total ?? 0, 2),
                    'estatus' => $f->estatus,
                    'fecha' => $f->created_at?->format('d/m/Y'),
                ]);
        }

        return Inertia::render('Panel/Dashboard/Index', $data);
    }

    // Genera datos de ejemplo para visualizar las gráficas del dashboard
    protected function registrosDemo(): array
    {
        $diasEs = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        $mesesEs = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

        $serie = function ($items) {
            $maxCount = max(collect($items)->max('count'), 1);
            return collect($items)->map(fn ($r) => $r + [
                'height' => $r['count'] > 0 ? round(($r['count'] / $maxCount) * 100) : 0,
            ])->values();
        };

        $inicioSemana = now()->startOfWeek();
        $conteosDia = [3, 5, 2, 4, 6, 1, 3];
        $dia = collect(range(0, 6))->map(function ($i) use ($inicioSemana, $diasEs, $conteosDia) {
            $d = $inicioSemana->copy()->addDays($i);
            return ['label' => $diasEs[$d->dayOfWeek], 'sub' => $d->format('d/m'), 'count' => $conteosDia[$i]];
        });

        $conteosSemana = [8, 12, 6, 10];
        $semana = collect(range(3, 0))->map(function ($i) use ($conteosSemana) {
            $inicio = now()->subWeeks($i)->startOfWeek();
            $fin = now()->subWeeks($i)->endOfWeek();
            return [
                'label' => 'Sem ' . (4 - $i),
                'sub' => $inicio->format('d/m') . ' - ' . $fin->format('d/m'),
                'count' => $conteosSemana[3 - $i],
            ];
        });

        $conteosMes = [12, 9, 15, 7, 18, 11];
        $mes = collect(range(5, 0))->map(function ($i) use ($mesesEs, $conteosMes) {
            $inicio = now()->subMonths($i)->startOfMonth();
            return ['label' => $mesesEs[$inicio->month - 1], 'sub' => $inicio->format('m/Y'), 'count' => $conteosMes[5 - $i]];
        });

        return [
            'dia' => $serie($dia),
            'semana' => $serie($semana),
            'mes' => $serie($mes),
        ];
    }

    // Genera datos de ejemplo para la gráfica de facturación
    protected function facturasDemo(): array
    {
        $diasEs = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        $mesesEs = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

        $serie = function ($items) {
            $maxCount = max(collect($items)->max('count'), 1);
            return collect($items)->map(fn ($r) => $r + [
                'height' => $r['count'] > 0 ? round(($r['count'] / $maxCount) * 100) : 0,
            ])->values();
        };

        $inicioSemana = now()->startOfWeek();
        $conteosDia = [1, 2, 1, 3, 2, 0, 1];
        $montosDia = [1520, 3240, 980, 4580, 2760, 0, 1210];
        $dia = collect(range(0, 6))->map(function ($i) use ($inicioSemana, $diasEs, $conteosDia, $montosDia) {
            $d = $inicioSemana->copy()->addDays($i);
            return ['label' => $diasEs[$d->dayOfWeek], 'sub' => $d->format('d/m'), 'count' => $conteosDia[$i], 'monto' => $montosDia[$i]];
        });

        $conteosSemana = [4, 6, 3, 5];
        $montosSemana = [8560, 12480, 6240, 9870];
        $semana = collect(range(3, 0))->map(function ($i) use ($conteosSemana, $montosSemana) {
            $inicio = now()->subWeeks($i)->startOfWeek();
            $fin = now()->subWeeks($i)->endOfWeek();
            return [
                'label' => 'Sem ' . (4 - $i),
                'sub' => $inicio->format('d/m') . ' - ' . $fin->format('d/m'),
                'count' => $conteosSemana[3 - $i],
                'monto' => $montosSemana[3 - $i],
            ];
        });

        $conteosMes = [6, 4, 8, 3, 9, 5];
        $montosMes = [14520, 9280, 18740, 7340, 21360, 11890];
        $mes = collect(range(5, 0))->map(function ($i) use ($mesesEs, $conteosMes, $montosMes) {
            $inicio = now()->subMonths($i)->startOfMonth();
            return ['label' => $mesesEs[$inicio->month - 1], 'sub' => $inicio->format('m/Y'), 'count' => $conteosMes[5 - $i], 'monto' => $montosMes[5 - $i]];
        });

        return [
            'dia' => $serie($dia),
            'semana' => $serie($semana),
            'mes' => $serie($mes),
        ];
    }
}
