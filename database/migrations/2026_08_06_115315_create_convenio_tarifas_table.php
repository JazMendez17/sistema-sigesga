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
        Schema::create('convenio_tarifas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('convenio_id')->constrained('convenios')->onDelete('cascade');
            $table->foreignId('servicio_id')->nullable()->constrained('catalogo_servicios')->onDelete('set null');
            $table->string('servicio', 150)->nullable();
            $table->string('alcance', 100)->nullable();
            $table->decimal('banderazo', 10, 2)->nullable();
            $table->integer('km_incluidos')->nullable();
            $table->decimal('costo_km_extra', 10, 2)->nullable();
            $table->decimal('tarifa_nocturna_recargo_pct', 5, 2)->nullable();
            $table->decimal('tarifa_domingo_festivo_recargo_pct', 5, 2)->nullable();
            $table->integer('minutos_espera_incluidos')->nullable();
            $table->decimal('costo_espera_adicional_hora', 10, 2)->nullable();
            $table->decimal('descuento_pct', 5, 2)->nullable();
            $table->string('tipo_descuento', 50)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('convenio_tarifas');
    }
};
