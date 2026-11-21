<?php

// Habilita rol `superadmin`, campos de seguridad en `usuarios`, `estado_previo` en
// `autorizaciones_cancelacion`, unifica ENUMs en MySQL/MariaDB y migra a borrado
// físico real (respaldando registros marcados con `eliminado = 1` en auditoria_eliminaciones
// y eliminando la columna `eliminado`).

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Orden seguro de hijas a padres para purgar filas marcadas con eliminado = 1 sin romper FKs. */
    private array $tablasEliminado = [
        'facturas',
        'notificaciones',
        'servicios',
        'cotizaciones',
        'unidad_mantenimientos',
        'unidades',
        'operadores',
        'convenio_tarifas',
        'convenio_conceptos_adicionales',
        'convenio_maniobras_especiales',
        'convenio_sla',
        'convenios',
        'tarifas_empresa',
        'aseguradora_contactos',
        'clientes',
        'aseguradoras',
        'catalogo_servicios',
        'usuarios',
        'empleados',
        'oficinas',
        'empresa_valores',
        'empresa_servicios',
        'empresas',
    ];

    public function up(): void
    {
        $driver = DB::getDriverName();

        // 1. Actualizar ENUMs en MySQL / MariaDB para bases ya existentes
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE usuarios MODIFY COLUMN rol ENUM('superadmin','admin','cotizador','operador','cliente') NOT NULL DEFAULT 'cliente'");
            DB::statement("ALTER TABLE notificaciones MODIFY COLUMN estado ENUM('pendiente','enviado','fallido','leido') NOT NULL DEFAULT 'pendiente'");
            DB::statement("ALTER TABLE autorizaciones_cancelacion MODIFY COLUMN estatus ENUM('pendiente','aprobada','cancelado_por_cotizador','cancelado_por_admin','rechazada') NOT NULL DEFAULT 'pendiente'");
        }

        // 2. Campos de seguridad y verificación en `usuarios`
        Schema::table('usuarios', function (Blueprint $table) {
            if (!Schema::hasColumn('usuarios', 'bloqueo_administrativo')) {
                $table->boolean('bloqueo_administrativo')->default(false)->after('cuenta_bloqueada');
            }
            if (!Schema::hasColumn('usuarios', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('email');
            }
        });

        // 3. Campo `estado_previo` en `autorizaciones_cancelacion`
        if (Schema::hasTable('autorizaciones_cancelacion') && !Schema::hasColumn('autorizaciones_cancelacion', 'estado_previo')) {
            Schema::table('autorizaciones_cancelacion', function (Blueprint $table) {
                $table->string('estado_previo', 50)->nullable()->after('estatus');
            });
        }

        // 4. Purgar registros marcados como eliminados lógicamente (respaldando antes en auditoría)
        //    y quitar la columna `eliminado` para trabajar con borrado físico real.
        Schema::disableForeignKeyConstraints();
        try {
            foreach ($this->tablasEliminado as $tabla) {
                if (!Schema::hasTable($tabla) || !Schema::hasColumn($tabla, 'eliminado')) {
                    continue;
                }

                $ocultos = DB::table($tabla)->where('eliminado', true)->get();
                foreach ($ocultos as $fila) {
                    if (Schema::hasTable('auditoria_eliminaciones')) {
                        $datosFila = (array) $fila;
                        foreach (['password', 'remember_token', 'codigo_desbloqueo', 'codigo_desbloqueo_expira', 'password_reset_token', 'password_reset_expires_at', 'api_key'] as $sensible) {
                            unset($datosFila[$sensible]);
                        }
                        DB::table('auditoria_eliminaciones')->insert([
                            'modelo' => $tabla,
                            'registro_id' => $fila->id ?? null,
                            'datos' => json_encode($datosFila, JSON_UNESCAPED_UNICODE),
                            'usuario_id' => null,
                            'created_at' => now(),
                        ]);
                    }
                }

                if ($ocultos->isNotEmpty()) {
                    DB::table($tabla)->where('eliminado', true)->delete();
                }

                Schema::table($tabla, function (Blueprint $table) {
                    $table->dropColumn('eliminado');
                });
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    public function down(): void
    {
        foreach ($this->tablasEliminado as $tabla) {
            if (Schema::hasTable($tabla) && !Schema::hasColumn($tabla, 'eliminado')) {
                Schema::table($tabla, function (Blueprint $table) {
                    $table->boolean('eliminado')->default(false);
                });
            }
        }

        if (Schema::hasTable('autorizaciones_cancelacion') && Schema::hasColumn('autorizaciones_cancelacion', 'estado_previo')) {
            Schema::table('autorizaciones_cancelacion', function (Blueprint $table) {
                $table->dropColumn('estado_previo');
            });
        }

        Schema::table('usuarios', function (Blueprint $table) {
            if (Schema::hasColumn('usuarios', 'bloqueo_administrativo')) {
                $table->dropColumn('bloqueo_administrativo');
            }
            if (Schema::hasColumn('usuarios', 'email_verified_at')) {
                $table->dropColumn('email_verified_at');
            }
        });
    }
};
