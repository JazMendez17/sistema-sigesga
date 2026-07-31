<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empleados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('oficina_id')->nullable()->constrained('oficinas')->onDelete('set null');
            $table->string('nombre', 100);
            $table->string('apellido_paterno', 100);
            $table->string('apellido_materno', 100)->nullable();
            $table->enum('sexo', ['M', 'F'])->nullable();
            $table->char('curp', 18)->nullable()->unique();
            $table->date('fecha_nacimiento')->nullable();
            $table->foreignId('direccion_id')->nullable()->constrained('direcciones')->onDelete('set null');
            $table->string('telefono', 20)->nullable();
            $table->string('telefono_local', 20)->nullable();
            $table->string('correo', 150)->nullable();
            $table->string('folio_ine', 20)->nullable();
            $table->string('nacionalidad', 50)->default('Mexicana');
            $table->string('puesto', 50)->nullable();
            $table->decimal('sueldo_diario', 10, 2)->default(0.00);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'telefono']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};
