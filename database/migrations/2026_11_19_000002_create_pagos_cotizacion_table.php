<?php

// Tabla de pagos con tarjeta para cotizaciones de clientes sin convenio con aseguradora.
// Cumple buenas prácticas PCI: nunca almacena PAN completo ni CVV; únicamente últimos 4 dígitos,
// marca, titular, vigencia y referencia de transacción.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pagos_cotizacion')) {
            Schema::create('pagos_cotizacion', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
                $table->foreignId('cotizacion_id')->constrained('cotizaciones')->onDelete('cascade');
                $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
                $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
                $table->string('referencia', 60)->unique();
                $table->string('metodo', 30)->default('tarjeta');
                $table->string('pasarela', 30)->default('simulado');
                $table->string('pasarela_transaccion_id', 120)->nullable();
                $table->decimal('monto', 10, 2);
                $table->string('moneda', 10)->default('MXN');
                $table->string('tarjeta_titular', 150);
                $table->string('tarjeta_marca', 30);
                $table->string('tarjeta_ultimos4', 4);
                $table->unsignedTinyInteger('tarjeta_exp_mes');
                $table->unsignedSmallInteger('tarjeta_exp_anio');
                $table->enum('estatus', ['pendiente', 'pagado', 'fallido', 'reembolsado'])->default('pagado');
                $table->timestamp('pagado_en')->nullable();
                $table->timestamps();

                $table->index(['empresa_id', 'estatus']);
                $table->index('cotizacion_id');
            });
        }

        Schema::table('cotizaciones', function (Blueprint $table) {
            if (!Schema::hasColumn('cotizaciones', 'requiere_pago_tarjeta')) {
                $table->boolean('requiere_pago_tarjeta')->default(false)->after('estatus');
            }
            if (!Schema::hasColumn('cotizaciones', 'estatus_pago')) {
                $table->string('estatus_pago', 30)->default('no_requerido')->after('requiere_pago_tarjeta');
            }
            if (!Schema::hasColumn('cotizaciones', 'pagada_at')) {
                $table->timestamp('pagada_at')->nullable()->after('estatus_pago');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos_cotizacion');

        Schema::table('cotizaciones', function (Blueprint $table) {
            $cols = array_values(array_filter(
                ['requiere_pago_tarjeta', 'estatus_pago', 'pagada_at'],
                fn ($col) => Schema::hasColumn('cotizaciones', $col)
            ));
            if ($cols !== []) {
                $table->dropColumn($cols);
            }
        });
    }
};
