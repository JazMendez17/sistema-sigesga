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
        Schema::table('tarifas_empresa', function (Blueprint $table) {
            $table->string('servicio', 150)->nullable()->after('nombre_tarifa');
            $table->string('alcance', 100)->nullable()->after('servicio');
            $table->decimal('costo_km_extra', 10, 2)->nullable()->after('km_incluidos');
            $table->decimal('tarifa_nocturna_recargo_pct', 5, 2)->nullable()->after('costo_km_extra');
            $table->decimal('tarifa_domingo_festivo_recargo_pct', 5, 2)->nullable()->after('tarifa_nocturna_recargo_pct');
            $table->integer('minutos_espera_incluidos')->nullable()->after('tarifa_domingo_festivo_recargo_pct');
            $table->decimal('costo_espera_adicional_hora', 10, 2)->nullable()->after('minutos_espera_incluidos');
            $table->decimal('descuento_pct', 5, 2)->nullable()->after('costo_espera_adicional_hora');
            $table->string('tipo_descuento', 50)->nullable()->after('descuento_pct');

            $table->string('tipo_ruta', 50)->nullable()->change();
            $table->decimal('costo_banderazo', 10, 2)->nullable()->change();
            $table->decimal('costo_km', 10, 2)->nullable()->change();
            $table->unsignedBigInteger('tipo_servicio_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tarifas_empresa', function (Blueprint $table) {
            $table->dropColumn(['servicio','alcance','costo_km_extra','tarifa_nocturna_recargo_pct','tarifa_domingo_festivo_recargo_pct','minutos_espera_incluidos','costo_espera_adicional_hora','descuento_pct','tipo_descuento']);
        });
    }
};
