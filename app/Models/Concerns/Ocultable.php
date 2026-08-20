<?php

// Trait para ocultar registros del sistema sin borrarlos de la base de datos.
// Política legal/auditoría: ninguna fila se elimina jamás; al "eliminar" un
// registro se marca `eliminado = true` y el scope global lo excluye de todas
// las consultas del sistema (listas, reportes, operaciones, login, etc.).
// La fila permanece íntegra en la BD y puede recuperarse con `conEliminados`.

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait Ocultable
{
    public static function bootOcultable(): void
    {
        static::addGlobalScope('no-eliminados', function (Builder $builder) {
            $builder->where($builder->getModel()->getTable() . '.eliminado', false);
        });
    }

    public function scopeConEliminados(Builder $query): Builder
    {
        return $query->withoutGlobalScope('no-eliminados');
    }
}