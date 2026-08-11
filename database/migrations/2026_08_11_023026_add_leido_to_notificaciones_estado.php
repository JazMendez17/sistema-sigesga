<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE notificaciones MODIFY COLUMN estado ENUM('pendiente','enviado','fallido','leido') NOT NULL DEFAULT 'pendiente'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE notificaciones MODIFY COLUMN estado ENUM('pendiente','enviado','fallido') NOT NULL DEFAULT 'pendiente'");
    }
};
