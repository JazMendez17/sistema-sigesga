<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('control_nomina', function (Blueprint $table) {
            $table->dropForeign(['empresa_id']);
            $table->dropForeign(['operador_id']);
        });

        Schema::table('control_nomina', function (Blueprint $table) {
            $table->unsignedBigInteger('empresa_id')->change();
            $table->unsignedBigInteger('operador_id')->change();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            $table->foreign('operador_id')->references('id')->on('operadores')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('control_nomina', function (Blueprint $table) {
            $table->dropForeign(['empresa_id']);
            $table->dropForeign(['operador_id']);
        });
    }
};
