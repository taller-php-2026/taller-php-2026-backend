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
        Schema::create('profesionales_servicios', function (Blueprint $table) {
            $table->id("idProfesionalServicio");
            $table->unsignedBigInteger('idProfesional');
            $table->foreign('idProfesional')->references('idUsuario')->on('profesionales')->onDelete('cascade');
            $table->unsignedBigInteger('idServicio');
            $table->foreign('idServicio')->references('idServicio')->on('servicios')->onDelete('cascade');
            $table->unique(['idProfesional', 'idServicio']);
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profesionales_servicios');
    }
};
