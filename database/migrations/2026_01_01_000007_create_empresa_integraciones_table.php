<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresa_integraciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->enum('proveedor', ['whatsapp', 'gmail', 'google_maps']);
            $table->string('api_key', 255)->nullable();
            $table->json('configuracion_json')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->unique(['empresa_id', 'proveedor']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresa_integraciones');
    }
};
