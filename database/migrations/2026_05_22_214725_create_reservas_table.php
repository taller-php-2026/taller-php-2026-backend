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
        Schema::create('reservas', function (Blueprint $table) {
            $table->id('idReserva');
            $table->dateTime('fechaReserva');
            $table->enum('estado', ['cancelada', 'pendiente', 'confirmada', 'enCurso', 'completada'])->default('pendiente');
            $table->string('comentarios')->nullable();
            $table->unsignedBigInteger('idPago')->nullable()->unique();
            $table->foreign('idPago')->references('idPago')->on('pagos')->onDelete('cascade');
            $table->unsignedBigInteger('idProfesional');
            $table->foreign('idProfesional')->references('idUsuario')->on('profesionales')->onDelete('cascade');
            $table->unsignedBigInteger('idCliente');
            $table->foreign('idCliente')->references('idUsuario')->on('clientes')->onDelete('cascade');
            $table->unsignedBigInteger('idServicio');
            $table->foreign('idServicio')->references('idServicio')->on('servicios')->onDelete('cascade');
            $table->unsignedBigInteger('idHorario');
            $table->foreign('idHorario')->references('idHorario')->on('horarios')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
