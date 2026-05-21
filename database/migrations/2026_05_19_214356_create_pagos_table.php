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
        Schema::create('pagos', function (Blueprint $table) {
            $table->id('idPago');
            $table->double('monto');
            $table->string('metodoPago')->nullable();
            $table->enum('estado', ['pendiente', 'aprobado', 'rechazado', 'cancelado', 'reembolsado'])->default('pendiente');
            $table->dateTime('fechaPago')->nullable();
            $table->string('referenciaExterna')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
