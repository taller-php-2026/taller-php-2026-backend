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
        Schema::create('reglas_disponibilidad', function (Blueprint $table) {
            $table->id('idRegla');
            $table->string('dia_semana');
            $table->time('horaInicio');
            $table->time('horaFin');
            $table->integer('pausaMinutos');
            $table->integer('bufferMinutos');
            $table->boolean('activa')->default(true);
            $table->foreignId('idAgenda')->constrained('agendas')->onDelete('cascade');
            $table->foreignId('idProfesional')->constrained('profesionales')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reglas_disponibilidad');
    }
};
