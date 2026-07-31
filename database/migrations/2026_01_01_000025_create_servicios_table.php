<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servicios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('cotizacion_id')->unique()->constrained('cotizaciones')->onDelete('cascade');
            $table->foreignId('operador_id')->constrained('operadores');
            $table->foreignId('unidad_id')->constrained('unidades');
            $table->foreignId('oficina_id')->nullable()->constrained('oficinas')->onDelete('set null');
            $table->enum('estado', ['asignado', 'inicio_servicio', 'en_sitio_origen', 'salida_destino', 'en_destino', 'finalizado', 'solicitud_cancelacion', 'cancelado'])->default('asignado');
            $table->integer('kms_salida')->nullable();
            $table->integer('kms_llegada_cliente')->nullable();
            $table->integer('kms_termino_servicio')->nullable();
            $table->integer('kms_regreso_base')->nullable();
            $table->integer('kms_cobrados_reales')->nullable();
            $table->decimal('cargo_zona_especial', 10, 2)->default(0.00);
            $table->decimal('costo_final_real', 12, 2)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'estado']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servicios');
    }
};
