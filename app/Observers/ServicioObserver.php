<?php

namespace App\Observers;

use App\Models\Notificacione;
use App\Models\Servicio;
use App\Models\Usuario;

// Observer: Notificaciones personalizadas por rol en cada evento del servicio
class ServicioObserver
{
    protected function actor(): string
    {
        return auth()->user()?->name ?? 'Sistema';
    }

    public function created(Servicio $servicio): void
    {
        $folio = $this->folio($servicio);
        $cliente = $servicio->cotizacion?->cliente;
        $clienteNombre = $cliente?->nombre ?? 'Cliente';
        $clienteUsuarioId = $cliente?->usuario_id;
        $operadorId = $servicio->operador?->empleado?->usuario?->id;
        $operadorNombre = $servicio->operador?->empleado?->nombre ?? 'Pendiente';
        $unidad = $servicio->unidad?->placas ?? 'Pendiente';
        $origen = $servicio->cotizacion?->origen_direccion ?? '—';
        $actor = $this->actor();

        if ($servicio->estado === 'asignado' && $operadorId) {
            // Servicio creado ya aprobado y asignado
            $this->notificar($servicio, $clienteUsuarioId,
                "Tu cotización {$folio} fue aprobada. Chofer: {$operadorNombre} | Unidad: {$unidad}.");
            $this->notificar($servicio, $operadorId,
                "Nuevo servicio asignado: {$folio}. Origen: {$origen}. Toca para ver la ruta.");
            $this->notificarRoles($servicio, ['admin', 'cotizador'],
                "{$actor} asignó al operador {$operadorNombre} (Unidad: {$unidad}) al servicio {$folio}.");
        } else {
            // Servicio creado en espera
            $this->notificar($servicio, $clienteUsuarioId,
                "Se ha registrado tu solicitud de servicio {$folio}. Está en revisión y espera de aprobación.");
            $this->notificarRoles($servicio, ['admin', 'cotizador'],
                "{$actor} registró la solicitud de servicio {$folio} del cliente {$clienteNombre}. Pendiente de revisión.");
        }
    }

    public function updated(Servicio $servicio): void
    {
        if (!$servicio->isDirty('estado')) return;

        $folio = $this->folio($servicio);
        $estado = $servicio->estado;
        $clienteUsuarioId = $servicio->cotizacion?->cliente?->usuario_id;
        $operadorId = $servicio->operador?->empleado?->usuario?->id;
        $operadorNombre = $servicio->operador?->empleado?->nombre ?? 'Operador';
        $unidad = $servicio->unidad?->placas ?? 'N/A';
        $kms = $servicio->kms_termino_servicio ?? '—';
        $actor = $this->actor();

        match ($estado) {
            'asignado' => $this->notificarAsignacion($servicio, $folio, $operadorNombre, $unidad, $clienteUsuarioId, $operadorId),
            'inicio_servicio' => $this->notificarFase($servicio, $folio, $operadorNombre, $clienteUsuarioId,
                'El operador va en camino a la ubicación de origen.',
                "{$actor} - El operador {$operadorNombre} inició el servicio {$folio} (En camino al origen)."),
            'en_sitio_origen' => $this->notificarFase($servicio, $folio, $operadorNombre, $clienteUsuarioId,
                'El operador ha llegado a la ubicación de origen.',
                "{$actor} - El operador {$operadorNombre} reportó llegada al origen en el servicio {$folio}."),
            'salida_destino' => $this->notificarFase($servicio, $folio, $operadorNombre, $clienteUsuarioId,
                'La grúa va en tránsito con tu vehículo hacia la dirección de destino.',
                "{$actor} - El operador {$operadorNombre} inició traslado hacia el destino en el servicio {$folio}."),
            'en_destino' => $this->notificarFase($servicio, $folio, $operadorNombre, $clienteUsuarioId,
                'Tu vehículo ha llegado a la dirección de destino.',
                "{$actor} - El operador {$operadorNombre} reportó llegada al destino en el servicio {$folio}."),
            'finalizado' => $this->notificarFinalizado($servicio, $folio, $operadorNombre, $kms, $clienteUsuarioId, $operadorId),
            'cancelado' => $this->notificarRoles($servicio, ['admin', 'cotizador'],
                "{$this->actor()} canceló el servicio {$folio}."),
            default => null
        };
    }

    // ─── Métodos de notificación ──────────────────────────────

    protected function notificarAsignacion($s, $folio, $opNombre, $unidad, $clienteId, $operadorId): void
    {
        $actor = $this->actor();
        $this->notificar($s, $clienteId, "Tu cotización {$folio} fue aprobada. Chofer: {$opNombre} | Unidad: {$unidad}.");
        $this->notificar($s, $operadorId, "Nuevo servicio asignado: {$folio}. Unidad: {$unidad}. Toca para ver la ruta.");
        $this->notificarRoles($s, ['admin', 'cotizador'], "{$actor} asignó al operador {$opNombre} (Unidad: {$unidad}) al servicio {$folio}.");
    }

    protected function notificarFase($s, $folio, $opNombre, $clienteId, string $msgCliente, string $msgAdmin): void
    {
        // Cliente recibe actualización
        $this->notificar($s, $clienteId, $msgCliente);
        // Admin y Cotizador reciben bitácora
        $this->notificarRoles($s, ['admin', 'cotizador'], $msgAdmin);
        // El operador NO recibe notificación de sus propias acciones en ruta
    }

    protected function notificarFinalizado($s, $folio, $opNombre, $kms, $clienteId, $operadorId): void
    {
        $this->notificar($s, $clienteId,
            "El servicio {$folio} ha sido finalizado. Evalúa nuestra atención: " . url('/panel/evaluar-servicio/' . $s->id));
        $this->notificar($s, $operadorId,
            "Servicio {$folio} concluido con éxito. Unidad disponible.");
        $this->notificarRoles($s, ['admin', 'cotizador'],
            "{$this->actor()} - El operador {$opNombre} finalizó el servicio {$folio}. Odómetro: {$kms} km.");
    }

    // ─── Helpers ──────────────────────────────────────────────

    protected function notificar(Servicio $s, ?int $usuarioId, string $mensaje): void
    {
        if (!$usuarioId) return;
        Notificacione::create([
            'empresa_id' => $s->empresa_id,
            'usuario_id' => $usuarioId,
            'mensaje' => $mensaje,
            'estado' => 'pendiente',
        ]);
    }

    protected function notificarRoles(Servicio $s, array $roles, string $mensaje): void
    {
        $ids = Usuario::where('empresa_id', $s->empresa_id)->whereIn('rol', $roles)->pluck('id');
        foreach ($ids as $uid) {
            $this->notificar($s, $uid, $mensaje);
        }
    }

    protected function folio(Servicio $s): string
    {
        return 'SVC-' . str_pad($s->id, 5, '0', STR_PAD_LEFT);
    }
}
