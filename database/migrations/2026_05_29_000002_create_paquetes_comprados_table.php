<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paquetes_comprados', function (Blueprint $table) {
            $table->id('idPaqueteComprado');
            $table->unsignedBigInteger('idPaqueteServicio');
            $table->foreign('idPaqueteServicio')->references('idPaqueteServicio')->on('paquetes_servicios')->onDelete('cascade');
            $table->unsignedBigInteger('idCliente');
            $table->foreign('idCliente')->references('idUsuario')->on('clientes')->onDelete('cascade');
            $table->unsignedBigInteger('idPago')->nullable()->unique();
            $table->foreign('idPago')->references('idPago')->on('pagos')->nullOnDelete();
            $table->integer('totalSesiones');
            $table->integer('sesionesUsadas')->default(0);
            $table->integer('sesionesRestantes');
            $table->double('precioCompra');
            $table->enum('estado', ['pendiente', 'activo', 'agotado', 'cancelado'])->default('pendiente');
            $table->dateTime('fechaCompra');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paquetes_comprados');
    }
};
