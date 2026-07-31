<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->foreignId('servicio_id')->unique()->constrained('servicios')->onDelete('cascade');
            $table->string('uuid_fiscal', 100)->nullable();
            $table->string('folio_factura', 50)->unique();
            $table->string('correo_envio_factura', 150);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('iva', 10, 2);
            $table->decimal('total', 12, 2);
            $table->string('xml_url', 255)->nullable();
            $table->string('pdf_url', 255)->nullable();
            $table->enum('estatus', ['vigente', 'cancelada'])->default('vigente');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'estatus']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facturas');
    }
};
