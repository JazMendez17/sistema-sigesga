<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->text('mensaje');
            $table->enum('canal', ['whatsapp', 'sistema_push', 'sms', 'email']);
            $table->enum('estado', ['pendiente', 'enviado', 'fallido'])->default('pendiente');
            $table->string('proveedor_mensaje_id', 150)->nullable();
            $table->unsignedTinyInteger('intentos_envio')->default(0);
            $table->text('error_detalle')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'estado']);
            $table->index('canal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
