<?php

// Utilidad de auditoría de eliminaciones físicas.
// Cada borrado físico de un registro se respalda en `auditoria_eliminaciones`
// omitiendo campos sensibles (`$hidden`) para proteger credenciales y tokens.

namespace App\Support;

use App\Models\AuditoriaEliminacione;
use Illuminate\Database\Eloquent\Model;

class Auditoria
{
    public static function registrar(Model $modelo, ?int $usuarioId = null): void
    {
        $atributos = $modelo->getAttributes();

        foreach ($modelo->getHidden() as $campoOculto) {
            unset($atributos[$campoOculto]);
        }

        AuditoriaEliminacione::create([
            'modelo' => $modelo->getTable(),
            'registro_id' => $modelo->getKey(),
            'datos' => $atributos,
            'usuario_id' => $usuarioId ?? auth()->id(),
            'created_at' => now(),
        ]);
    }
}
