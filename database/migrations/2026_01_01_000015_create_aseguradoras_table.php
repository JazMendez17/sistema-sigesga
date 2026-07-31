<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aseguradoras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->string('nombre', 150);
            $table->string('nombre_comercial', 150)->nullable();
            $table->string('rfc', 13)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('rfc');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aseguradoras');
    }
};
