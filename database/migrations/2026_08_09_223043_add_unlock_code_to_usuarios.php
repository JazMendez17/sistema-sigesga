<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->string('codigo_desbloqueo', 6)->nullable()->after('cuenta_bloqueada');
            $table->timestamp('codigo_desbloqueo_expira')->nullable()->after('codigo_desbloqueo');
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn(['codigo_desbloqueo', 'codigo_desbloqueo_expira']);
        });
    }
};
