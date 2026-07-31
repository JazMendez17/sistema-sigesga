<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('convenio_unidades_autorizadas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('convenio_id')->constrained('convenios')->onDelete('cascade');
            $table->enum('tipo_grua', ['A', 'B', 'C', 'D']);
            $table->decimal('peso_maximo_kg', 10, 2)->nullable();
            $table->enum('equipamiento', ['plataforma_hidraulica', 'gancho_arrastre', 'ambos']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('convenio_unidades_autorizadas');
    }
};
