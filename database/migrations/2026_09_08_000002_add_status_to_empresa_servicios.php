<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresa_servicios', function (Blueprint $table) {
            $table->boolean('activo')->default(true)->after('orden');
            $table->string('color', 20)->nullable()->after('activo');
        });
    }

    public function down(): void
    {
        Schema::table('empresa_servicios', function (Blueprint $table) {
            $table->dropColumn(['activo', 'color']);
        });
    }
};
