<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('autorizaciones_cancelacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('servicio_id')->constrained('servicios')->onDelete('cascade');
            $table->foreignId('usuario_solicitante_id')->constrained('usuarios');
            $table->foreignId('usuario_resolutor_id')->nullable()->constrained('usuarios');
            $table->text('motivo_cancelacion');
            $table->enum('tipo_incidencia', ['cliente_cancela', 'operador_siniestro', 'falla_mecanica', 'unidad_ponchada', 'otro']);
            $table->enum('estatus', ['pendiente', 'cancelado_por_cotizador', 'cancelado_por_admin', 'rechazada'])->default('pendiente');
            $table->dateTime('fecha_solicitud');
            $table->dateTime('fecha_resolucion')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('autorizaciones_cancelacion');
    }
};
