<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('siglas', 20)->nullable();
            $table->string('slogan', 255)->nullable();
            $table->string('logo', 255)->nullable();
            $table->string('imagen_fondo', 255)->nullable();
            $table->string('texto_derechos', 255)->nullable();
            $table->string('color_primario', 20)->nullable();
            $table->string('color_secundario', 20)->nullable();
            $table->string('color_fondo', 20)->nullable();
            $table->string('color_texto', 20)->nullable();
            $table->string('tipografia', 50)->default('Roboto')->nullable();
            $table->boolean('modo_oscuro')->default(false);
            $table->string('telefono_contacto', 20)->nullable();
            $table->string('email_contacto', 150)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
