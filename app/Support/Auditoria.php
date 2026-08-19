<?php

// Utilidad de auditoría de eliminaciones.
// Cada borrado físico de un registro se respalda en `auditoria_eliminaciones`
// con un snapshot completo de sus datos, por política legal y de auditoría.

namespace App\Support;

use App\Models\AuditoriaEliminacione;
use Illuminate\Database\Eloquent\Model;

class Auditoria
{
    public static function registrar(Model $modelo, ?int $usuarioId = null): void
    {
        AuditoriaEliminacione::create([
            'modelo' => $modelo->getTable(),
            'registro_id' => $modelo->getKey(),
            'datos' => $modelo->getAttributes(),
            'usuario_id' => $usuarioId ?? auth()->id(),
            'created_at' => now(),
        ]);
    }
}