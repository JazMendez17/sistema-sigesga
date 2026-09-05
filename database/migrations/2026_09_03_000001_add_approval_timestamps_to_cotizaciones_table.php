<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->timestamp('cliente_aprobada_at')->nullable()->after('estatus');
            $table->timestamp('aprobada_internamente_at')->nullable()->after('cliente_aprobada_at');
        });
    }

    public function down(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->dropColumn(['cliente_aprobada_at', 'aprobada_internamente_at']);
        });
    }
};
