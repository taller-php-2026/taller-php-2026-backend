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
        Schema::create('resenas', function (Blueprint $table) {
            $table->id('idResena');
            $table->integer('calificacion');
            $table->text('comentario')->nullable();
            $table->date('fecha'); 
            $table->unsignedBigInteger('idProfesional');
            $table->foreign('idProfesional')->references('idUsuario')->on('profesionales')->onDelete('cascade');
            $table->unsignedBigInteger('idCliente');
            $table->foreign('idCliente')->references('idUsuario')->on('clientes')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resenas');
    }
};
