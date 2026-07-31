<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('usuario_id')->nullable()->unique()->constrained('usuarios')->onDelete('set null');
            $table->foreignId('aseguradora_id')->nullable()->constrained('aseguradoras')->onDelete('set null');
            $table->enum('tipo_cliente', ['persona_fisica', 'persona_moral'])->default('persona_fisica');
            $table->string('nombre', 150);
            $table->string('apellido_paterno', 100)->nullable();
            $table->string('apellido_materno', 100)->nullable();
            $table->enum('sexo', ['M', 'F'])->nullable();
            $table->char('curp', 18)->nullable()->unique();
            $table->date('fecha_nacimiento')->nullable();
            $table->foreignId('direccion_id')->nullable()->constrained('direcciones')->onDelete('set null');
            $table->string('telefono', 20)->nullable();
            $table->string('telefono_local', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('folio_ine', 20)->nullable();
            $table->string('nacionalidad', 50)->default('Mexicana');
            $table->string('contacto_enlace', 150)->nullable();
            $table->string('numero_poliza', 50)->nullable();
            $table->string('tipo_cobertura_poliza', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'numero_poliza']);
            $table->index(['empresa_id', 'telefono']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
