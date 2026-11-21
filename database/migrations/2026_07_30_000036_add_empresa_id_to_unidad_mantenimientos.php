<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('unidad_mantenimientos', 'empresa_id')) {
            Schema::table('unidad_mantenimientos', function (Blueprint $table) {
                $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
                $table->index('empresa_id');
            });
        }
    }

    public function down(): void
    {
        // La columna empresa_id ya se crea en la migración inicial 000013; no se elimina aquí.
    }
};
