<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->onDelete('set null');
            $table->foreignId('convenio_aplicado_id')->nullable()->constrained('convenios')->onDelete('set null');
            $table->foreignId('tarifa_empresa_aplicada_id')->nullable()->constrained('tarifas_empresa')->onDelete('set null');
            $table->foreignId('usuario_creador_id')->constrained('usuarios');
            $table->string('folio', 50)->unique();
            $table->foreignId('tipo_servicio_id')->constrained('catalogo_servicios');
            $table->text('origen_direccion');
            $table->decimal('origen_lat', 10, 8)->nullable();
            $table->decimal('origen_lng', 11, 8)->nullable();
            $table->text('destino_direccion');
            $table->decimal('destino_lat', 10, 8)->nullable();
            $table->decimal('destino_lng', 11, 8)->nullable();
            $table->text('ruta_polyline')->nullable();
            $table->decimal('distancia_km', 10, 2);
            $table->integer('tiempo_estimado_minutos')->nullable();
            $table->boolean('incluye_peajes')->default(false);
            $table->decimal('costo_aprox_casetas', 10, 2)->default(0.00);
            $table->decimal('cargo_zona_especial', 10, 2)->default(0.00);
            $table->decimal('costo_banderazo', 10, 2);
            $table->decimal('costo_km', 10, 2);
            $table->decimal('km_excedente', 10, 2)->default(0.00);
            $table->decimal('costo_total', 12, 2);
            $table->enum('estatus', ['pendiente', 'aprobado', 'rechazado'])->default('pendiente');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'estatus']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cotizaciones');
    }
};
