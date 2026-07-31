<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unidad_mantenimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('unidad_id')->constrained('unidades')->onDelete('cascade');
            $table->index('empresa_id');
            $table->string('tipo', 100);
            $table->date('fecha');
            $table->integer('kilometraje')->nullable();
            $table->decimal('costo', 10, 2)->default(0.00);
            $table->date('proximo_mantenimiento_fecha')->nullable();
            $table->integer('proximo_mantenimiento_km')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unidad_mantenimientos');
    }
};
