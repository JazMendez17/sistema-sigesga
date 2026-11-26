<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos_cotizacion_intentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('cotizacion_id')->constrained('cotizaciones')->cascadeOnDelete();
            $table->foreignId('pago_cotizacion_id')->nullable()->constrained('pagos_cotizacion')->nullOnDelete();
            $table->string('idempotency_key', 64)->unique();
            $table->string('provider_idempotency_key', 36)->unique();
            $table->string('referencia', 60)->unique();
            $table->string('estado', 20)->default('procesando');
            $table->string('mercadopago_order_id', 120)->nullable();
            $table->string('mercadopago_payment_id', 120)->nullable();
            $table->longText('request_payload')->nullable();
            $table->text('mensaje_error')->nullable();
            $table->timestamp('rechazo_notificado_en')->nullable();
            $table->timestamps();

            $table->index(['cotizacion_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos_cotizacion_intentos');
    }
};
