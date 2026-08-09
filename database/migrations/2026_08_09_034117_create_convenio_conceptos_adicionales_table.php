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
        Schema::create('convenio_conceptos_adicionales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('convenio_id')->unique()->constrained('convenios')->onDelete('cascade');
            $table->boolean('cubre_casetas')->default(false);
            $table->string('forma_pago_casetas', 100)->nullable();
            $table->decimal('costo_estadia_dia', 10, 2)->default(0.00);
            $table->integer('dias_gracia_estadia')->default(0);
            $table->decimal('costo_resguardo_nocturno', 10, 2)->default(0.00);
            $table->boolean('genera_cargo_cliente_final')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('convenio_conceptos_adicionales');
    }
};
