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
        Schema::table('convenio_maniobras_especiales', function (Blueprint $table) {
            $table->boolean('aplica')->default(true)->after('concepto');
            $table->string('forma_cobro', 50)->nullable()->after('aplica');
        });
    }

    public function down(): void
    {
        Schema::table('convenio_maniobras_especiales', function (Blueprint $table) {
            $table->dropColumn(['aplica', 'forma_cobro']);
        });
    }
};
