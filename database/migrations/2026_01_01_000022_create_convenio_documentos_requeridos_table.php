<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('convenio_documentos_requeridos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('convenio_id')->constrained('convenios')->onDelete('cascade');
            $table->string('documento', 150);
            $table->boolean('obligatorio')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('convenio_documentos_requeridos');
    }
};
