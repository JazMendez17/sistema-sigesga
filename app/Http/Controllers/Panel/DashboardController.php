<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Cotizacione;
use App\Models\Servicio;
use App\Models\Cliente;
use App\Models\Notificacione;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        $data = ['role' => $user->rol];

        if (in_array($user->rol, ['admin', 'cotizador'])) {
            $data['kpis'] = [
                ['title' => 'Cotizaciones Pendientes', 'value' => (string) Cotizacione::where('empresa_id', $empresaId)->where('estatus', 'pendiente')->count(), 'icon' => 'clipboard', 'color' => '#4F46E5'],
                ['title' => 'Servicios Activos', 'value' => (string) Servicio::where('empresa_id', $empresaId)->whereIn('estado', ['asignado', 'en_curso'])->count(), 'icon' => 'truck', 'color' => '#059669'],
                ['title' => 'Operadores Disponibles', 'value' => (string) \App\Models\Operadore::where('empresa_id', $empresaId)->where('disponible', true)->count(), 'icon' => 'users', 'color' => '#D97706'],
                ['title' => 'Ingresos del Mes', 'value' => '$' . number_format(Servicio::where('empresa_id', $empresaId)->whereMonth('created_at', now()->month)->sum('costo_final_real'), 2), 'icon' => 'dollar', 'color' => '#7C3AED'],
            ];

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
                    'time' => $c->created_at?->diffForHumans() ?? '—',
                    'status' => $c->estatus ?? 'pendiente',
                ]);
            }
            foreach ($servicios as $s) {
                $items->push([
                    'id' => $s->id,
                    'type' => 'servicio',
                    'title' => 'Servicio #' . str_pad($s->id, 5, '0', STR_PAD_LEFT),
                    'description' => ($s->cotizacion?->cliente?->nombre ?? '—') . ' — ' . ($s->cotizacion?->tipoServicio?->nombre ?? '—'),
                    'time' => $s->created_at?->diffForHumans() ?? '—',
                    'status' => $s->estado ?? 'pendiente',
                ]);
            }
            $data['recentActivity'] = $items->sortByDesc('id')->take(5)->values();
        }

        if ($user->rol === 'operador') {
            $data['serviciosHoy'] = Servicio::with('cotizacion.cliente')
                ->where('empresa_id', $empresaId)
                ->whereDate('created_at', today())
                ->get()
                ->map(fn ($s) => [
                    'id' => $s->id,
                    'cliente' => $s->cotizacion?->cliente?->nombre ?? '—',
                    'ruta' => ($s->cotizacion?->origen_direccion ?? '—') . ' → ' . ($s->cotizacion?->destino_direccion ?? '—'),
                    'horario' => $s->created_at?->format('H:i') . ' - ' . ($s->updated_at?->format('H:i') ?? '—'),
                    'status' => $s->estado ?? 'asignado',
                ]);
        }

        if ($user->rol === 'cliente') {
            $cliente = Cliente::where('usuario_id', $user->id)->first();
            $data['historialCliente'] = Servicio::with('cotizacion')
                ->whereHas('cotizacion', fn ($q) => $q->where('cliente_id', $cliente?->id))
                ->latest()
                ->take(5)
                ->get()
                ->map(fn ($s) => [
                    'id' => $s->id,
                    'servicio' => $s->cotizacion?->tipoServicio?->nombre ?? 'Transporte',
                    'fecha' => $s->created_at?->format('d M Y'),
                    'status' => $s->estado ?? 'finalizado',
                    'monto' => '$' . number_format($s->costo_final_real ?? 0, 2),
                ]);
        }

        return Inertia::render('Panel/Dashboard/Index', $data);
    }
}
