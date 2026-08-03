<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('unidades_ubicaciones', function (Blueprint $table) {
            $table->dropForeign(['unidad_id']);
            $table->dropForeign(['servicio_id']);
            $table->dropIndex(['unidad_id', 'registrado_en']);
        });

        Schema::table('unidades_ubicaciones', function (Blueprint $table) {
            $table->unsignedBigInteger('unidad_id')->change();
            $table->unsignedBigInteger('servicio_id')->nullable()->change();
            $table->foreign('unidad_id')->references('id')->on('unidades')->onDelete('cascade');
            $table->foreign('servicio_id')->references('id')->on('servicios')->onDelete('set null');
            $table->index(['unidad_id', 'registrado_en']);
        });
    }

    public function down(): void
    {
        Schema::table('unidades_ubicaciones', function (Blueprint $table) {
            $table->dropForeign(['unidad_id']);
            $table->dropForeign(['servicio_id']);
            $table->dropIndex(['unidad_id', 'registrado_en']);
        });
    }
};
