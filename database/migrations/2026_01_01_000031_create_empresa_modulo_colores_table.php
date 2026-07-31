<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresa_modulo_colores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->string('modulo', 50);
            $table->string('color', 20);
            $table->timestamps();

            $table->unique(['empresa_id', 'modulo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresa_modulo_colores');
    }
};
