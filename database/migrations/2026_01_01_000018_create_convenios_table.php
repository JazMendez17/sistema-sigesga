<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('convenios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('aseguradora_id')->constrained('aseguradoras')->onDelete('cascade');
            $table->string('nombre_convenio_poliza', 150);
            $table->foreignId('tipo_servicio_id')->constrained('catalogo_servicios');
            $table->enum('tipo_ruta', ['local', 'foraneo']);
            $table->string('tipo_cobertura', 100);
            $table->string('alcance_geografico', 255);
            $table->decimal('costo_banderazo', 10, 2);
            $table->decimal('costo_km', 10, 2);
            $table->decimal('km_seguros_incluidos', 10, 2)->default(0.00);
            $table->decimal('km_maximo_amparado', 10, 2)->nullable();
            $table->decimal('tope_presupuesto', 10, 2)->nullable();
            $table->boolean('cubre_casetas_peaje')->default(false);
            $table->integer('dias_credito')->nullable();
            $table->text('proceso_envio_facturas')->nullable();
            $table->enum('estatus', ['vigente', 'vencido', 'en_negociacion', 'cancelado'])->default('vigente');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'estatus']);
            $table->index('tipo_ruta');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('convenios');
    }
};
