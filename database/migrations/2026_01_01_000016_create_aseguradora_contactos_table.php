<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aseguradora_contactos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aseguradora_id')->constrained('aseguradoras')->onDelete('cascade');
            $table->enum('departamento', ['cabina', 'siniestros', 'asistencia_vial', 'otro']);
            $table->string('nombre_contacto', 150)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('red_social', 150)->nullable();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aseguradora_contactos');
    }
};
