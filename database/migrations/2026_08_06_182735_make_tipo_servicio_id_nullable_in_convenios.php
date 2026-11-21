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
        Schema::table('convenios', function (Blueprint $table) {
            $table->unsignedBigInteger('tipo_servicio_id')->nullable()->change();
            $table->string('tipo_ruta', 50)->nullable()->change();
            $table->string('tipo_cobertura', 100)->nullable()->change();
            $table->string('alcance_geografico', 255)->nullable()->change();
            $table->decimal('costo_banderazo', 10, 2)->nullable()->change();
            $table->decimal('costo_km', 10, 2)->nullable()->change();
            $table->decimal('km_seguros_incluidos', 10, 2)->nullable()->change();
            $table->decimal('km_maximo_amparado', 10, 2)->nullable()->change();
            $table->decimal('tope_presupuesto', 12, 2)->nullable()->change();
            $table->integer('dias_credito')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Mantener nullable para evitar pérdida de datos o fallos de rollback cuando hay registros parciales
    }
};
