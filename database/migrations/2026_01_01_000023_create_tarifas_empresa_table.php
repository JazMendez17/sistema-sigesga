<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarifas_empresa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('tipo_servicio_id')->constrained('catalogo_servicios');
            $table->string('nombre_tarifa', 150);
            $table->enum('tipo_ruta', ['local', 'foraneo']);
            $table->decimal('costo_banderazo', 10, 2);
            $table->decimal('costo_km', 10, 2);
            $table->decimal('km_incluidos', 10, 2)->default(0.00);
            $table->boolean('cubre_casetas_peaje')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarifas_empresa');
    }
};
