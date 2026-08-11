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
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->decimal('subtotal', 12, 2)->nullable()->after('costo_total');
            $table->decimal('monto_descuento', 12, 2)->nullable()->after('subtotal');
            $table->decimal('monto_iva', 12, 2)->nullable()->after('monto_descuento');
            $table->decimal('descuento_pct', 5, 2)->nullable()->after('monto_iva');
            $table->decimal('km_incluidos', 10, 2)->nullable()->after('descuento_pct');
        });
    }

    public function down(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'monto_descuento', 'monto_iva', 'descuento_pct', 'km_incluidos']);
        });
    }
};
