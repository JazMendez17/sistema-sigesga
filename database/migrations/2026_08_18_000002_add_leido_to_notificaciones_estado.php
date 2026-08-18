<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE notificaciones MODIFY COLUMN estado ENUM('pendiente','enviado','fallido','leido') NOT NULL DEFAULT 'pendiente'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE notificaciones MODIFY COLUMN estado ENUM('pendiente','enviado','fallido') NOT NULL DEFAULT 'pendiente'");
    }
};