<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('empresa_integraciones')) {
            return;
        }

        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE empresa_integraciones MODIFY COLUMN proveedor VARCHAR(50) NOT NULL, MODIFY COLUMN api_key TEXT NULL");
            return;
        }

        // En SQLite se cambia la columna a string para eliminar la restricción CHECK del enum antiguo.
        Schema::table('empresa_integraciones', function (Blueprint $table) {
            $table->string('proveedor', 50)->change();
            $table->text('api_key')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('empresa_integraciones')) {
            return;
        }

        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::table('empresa_integraciones')
                ->whereNotIn('proveedor', ['whatsapp', 'gmail', 'google_maps'])
                ->delete();
            DB::statement("ALTER TABLE empresa_integraciones MODIFY COLUMN proveedor ENUM('whatsapp', 'gmail', 'google_maps') NOT NULL");
        }
    }
};
