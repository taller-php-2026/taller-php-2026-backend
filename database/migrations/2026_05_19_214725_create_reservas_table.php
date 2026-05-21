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
            $table->time('fechaReserva');
            $table->enum('estado', ['cancelada', 'pendiente', 'confirmada', 'enCurso', 'completada'])->default('pendiente');
            $table->string('comentarios')->nullable();
            $table->foreignId('idPago')->constrained('pagos')->onDelete('cascade');
            $table->foreignId('idProfesional')->constrained('profesionales')->onDelete('cascade');
            $table->foreignId('idCliente')->constrained('clientes')->onDelete('cascade');
            $table->foreignId('idServicio')->constrained('servicios')->onDelete('cascade');
            $table->foreignId('idHorario')->constrained('horarios')->onDelete('cascade');
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
