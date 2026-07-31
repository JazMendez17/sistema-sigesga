<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('oficina_id')->nullable()->constrained('oficinas')->onDelete('set null');
            $table->foreignId('operador_asignado_id')->nullable()->unique()->constrained('operadores')->onDelete('set null');
            $table->string('marca', 50);
            $table->string('tipo', 50);
            $table->string('modelo', 45)->nullable();
            $table->string('placas', 20);
            $table->string('numero_economico', 50);
            $table->date('seguro_vencimiento')->nullable();
            $table->string('estado_emplacado', 50)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'placas']);
            $table->index(['empresa_id', 'numero_economico']);
            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unidades');
    }
};
