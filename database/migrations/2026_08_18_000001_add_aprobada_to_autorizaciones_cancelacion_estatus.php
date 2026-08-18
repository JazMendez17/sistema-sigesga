<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE autorizaciones_cancelacion MODIFY COLUMN estatus ENUM('pendiente','aprobada','cancelado_por_cotizador','cancelado_por_admin','rechazada') NOT NULL DEFAULT 'pendiente'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE autorizaciones_cancelacion MODIFY COLUMN estatus ENUM('pendiente','cancelado_por_cotizador','cancelado_por_admin','rechazada') NOT NULL DEFAULT 'pendiente'");
    }
};