<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('convenio_maniobras_especiales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('convenio_id')->constrained('convenios')->onDelete('cascade');
            $table->string('concepto', 150);
            $table->decimal('costo', 10, 2);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('convenio_maniobras_especiales');
    }
};
