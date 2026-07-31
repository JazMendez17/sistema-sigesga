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
        Schema::table('unidad_mantenimientos', function (Blueprint $table) {
            $table->dropForeign(['empresa_id']);
            $table->dropIndex(['empresa_id']);
            $table->dropColumn('empresa_id');
        });
    }
};
