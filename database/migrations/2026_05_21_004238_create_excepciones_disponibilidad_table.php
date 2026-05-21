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
        Schema::create('excepciones_disponibilidad', function (Blueprint $table) {
            $table->id('idExcepcion');
            $table->date('fecha');
            $table->time('horaInicio');
            $table->time('horaFin');
            $table->string('motivo')->nullable();
            $table->foreignId('idAgenda')->constrained('agendas')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('excepciones_disponibilidad');
    }
};
