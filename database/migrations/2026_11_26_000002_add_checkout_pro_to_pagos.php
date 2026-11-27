<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresa_integraciones', function (Blueprint $table) {
            if (!Schema::hasColumn('empresa_integraciones', 'webhook_secret')) {
                $table->text('webhook_secret')->nullable()->after('api_key');
            }
        });

        Schema::table('pagos_cotizacion_intentos', function (Blueprint $table) {
            if (!Schema::hasColumn('pagos_cotizacion_intentos', 'mercadopago_preference_id')) {
                $table->string('mercadopago_preference_id', 120)->nullable();
            }

            if (!Schema::hasColumn('pagos_cotizacion_intentos', 'checkout_url')) {
                $table->text('checkout_url')->nullable();
            }

            if (!Schema::hasColumn('pagos_cotizacion_intentos', 'ultimo_evento_en')) {
                $table->timestamp('ultimo_evento_en')->nullable();
            }
        });

        Schema::table('pagos_cotizacion', function (Blueprint $table) {
            $table->string('tarjeta_titular', 150)->nullable()->change();
            $table->string('tarjeta_marca', 30)->nullable()->change();
            $table->string('tarjeta_ultimos4', 4)->nullable()->change();
            $table->unsignedTinyInteger('tarjeta_exp_mes')->nullable()->change();
            $table->unsignedSmallInteger('tarjeta_exp_anio')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('pagos_cotizacion_intentos', function (Blueprint $table) {
            $table->dropColumn([
                'mercadopago_preference_id',
                'checkout_url',
                'ultimo_evento_en',
            ]);
        });

        Schema::table('empresa_integraciones', function (Blueprint $table) {
            $table->dropColumn('webhook_secret');
        });

        $tienePagosSinTarjeta = DB::table('pagos_cotizacion')
            ->where(function ($query) {
                $query->whereNull('tarjeta_titular')
                    ->orWhereNull('tarjeta_marca')
                    ->orWhereNull('tarjeta_ultimos4')
                    ->orWhereNull('tarjeta_exp_mes')
                    ->orWhereNull('tarjeta_exp_anio');
            })
            ->exists();

        if (!$tienePagosSinTarjeta) {
            Schema::table('pagos_cotizacion', function (Blueprint $table) {
                $table->string('tarjeta_titular', 150)->nullable(false)->change();
                $table->string('tarjeta_marca', 30)->nullable(false)->change();
                $table->string('tarjeta_ultimos4', 4)->nullable(false)->change();
                $table->unsignedTinyInteger('tarjeta_exp_mes')->nullable(false)->change();
                $table->unsignedSmallInteger('tarjeta_exp_anio')->nullable(false)->change();
            });
        }
    }
};
