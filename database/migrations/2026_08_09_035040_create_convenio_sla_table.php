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
        Schema::create('convenio_sla', function (Blueprint $table) {
            $table->id();
            $table->foreignId('convenio_id')->unique()->constrained('convenios')->onDelete('cascade');
            $table->integer('tiempo_max_respuesta_urbano_min');
            $table->integer('tiempo_max_respuesta_carretera_min');
            $table->string('disponibilidad', 50)->default('24/7');
            $table->text('penalizacion_incumplimiento')->nullable();
            $table->string('protocolo_asignacion', 150)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('convenio_sla');
    }
};
