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
            $table->string('codigo_convenio', 50)->nullable()->after('nombre_convenio_poliza');
            $table->date('fecha_inicio')->nullable()->after('codigo_convenio');
            $table->date('fecha_fin')->nullable()->after('fecha_inicio');
            $table->boolean('renovacion_automatica')->default(false)->after('fecha_fin');
            $table->boolean('exclusivo')->default(false)->after('renovacion_automatica');
            $table->string('periodicidad_corte', 50)->nullable()->after('dias_credito');
            $table->boolean('requiere_folio_cfdi')->default(false)->after('periodicidad_corte');
            $table->boolean('iva_incluido')->default(false)->after('requiere_folio_cfdi');
            $table->decimal('tope_credito', 12, 2)->nullable()->after('iva_incluido');
            $table->integer('aviso_previo_terminacion_dias')->nullable()->after('tope_credito');
        });
    }

    public function down(): void
    {
        Schema::table('convenios', function (Blueprint $table) {
            $table->dropColumn([
                'codigo_convenio', 'fecha_inicio', 'fecha_fin', 'renovacion_automatica',
                'exclusivo', 'periodicidad_corte', 'requiere_folio_cfdi', 'iva_incluido',
                'tope_credito', 'aviso_previo_terminacion_dias',
            ]);
        });
    }
};
