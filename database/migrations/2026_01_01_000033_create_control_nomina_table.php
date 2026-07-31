<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('control_nomina', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('operador_id')->constrained('operadores')->onDelete('cascade');
            $table->date('fecha_desde');
            $table->date('fecha_hasta');
            $table->decimal('sueldo_base_semanal', 10, 2);
            $table->decimal('bonos_servicios', 10, 2)->default(0.00);
            $table->decimal('descuentos_prestamos', 10, 2)->default(0.00);
            $table->decimal('total_neto_a_pagar', 14, 2)->virtualAs('sueldo_base_semanal + bonos_servicios - descuentos_prestamos')->stored();
            $table->enum('estatus', ['pendiente', 'pagado'])->default('pendiente');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('control_nomina');
    }
};
