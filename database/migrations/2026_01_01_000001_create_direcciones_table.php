<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('direcciones', function (Blueprint $table) {
            $table->id();
            $table->string('calle', 150);
            $table->string('numero_exterior', 20);
            $table->string('numero_interior', 20)->nullable();
            $table->string('colonia', 100);
            $table->string('codigo_postal', 10);
            $table->string('municipio_alcaldia', 100);
            $table->string('ciudad', 100);
            $table->string('estado', 60);
            $table->string('pais', 60)->default('México');
            $table->string('referencias', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('direcciones');
    }
};
