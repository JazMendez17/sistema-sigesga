<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('empleado_id')->nullable()->unique()->constrained('empleados')->onDelete('set null');
            $table->string('name', 150);
            $table->string('email', 150)->unique();
            $table->string('password', 255);
            $table->string('password_reset_token', 255)->nullable();
            $table->timestamp('password_reset_expires_at')->nullable();
            $table->enum('rol', ['admin', 'cotizador', 'operador', 'cliente']);
            $table->unsignedTinyInteger('intentos_fallidos')->default(0);
            $table->boolean('cuenta_bloqueada')->default(false);
            $table->timestamp('bloqueada_en')->nullable();
            $table->foreignId('desbloqueada_por')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'rol']);
            $table->index('cuenta_bloqueada');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
