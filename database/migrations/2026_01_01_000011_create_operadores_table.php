<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operadores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('empleado_id')->unique()->constrained('empleados')->onDelete('cascade');
            $table->string('tipo_licencia', 50);
            $table->string('numero_licencia', 50);
            $table->date('fecha_expedicion');
            $table->date('fecha_vigencia');
            $table->boolean('disponible')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'disponible']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operadores');
    }
};
