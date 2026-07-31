<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oficinas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->string('nombre', 100);
            $table->foreignId('direccion_id')->nullable()->constrained('direcciones')->onDelete('set null');
            $table->string('telefono', 25)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('encargado', 150)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oficinas');
    }
};
