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
        Schema::create('rango_horarios', function (Blueprint $table) {
            $table->id('idRango');
            $table->enum('diaSemana', ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo']);
            $table->time('horaInicio');
            $table->time('horaFin');
            $table->unsignedBigInteger('idCiclo');
            $table->foreign('idCiclo')->references('idCiclo')->on('ciclos')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rango_horarios');
    }
};
