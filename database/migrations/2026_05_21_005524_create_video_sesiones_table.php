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
        Schema::create('video_sesiones', function (Blueprint $table) {
            $table->id();
            $table->string('proveedor');
            $table->string('url');
            $table->string('nombreSala');
            $table->dateTime('fechaHoraInicio');
            $table->dateTime('fechaHoraFin')->nullable();
            $table->enum('estado', ['programada', 'enCurso', 'finalizada'])->default('programada');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_sesiones');
    }
};
