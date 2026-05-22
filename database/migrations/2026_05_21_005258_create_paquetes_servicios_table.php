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
        Schema::create('paquetes_servicios', function (Blueprint $table) {
            $table->id('idPaqueteServicio');
            $table->unsignedBigInteger('idServicio')->unique();
            $table->foreign('idServicio')->references('idServicio')->on('servicios')->onDelete('cascade');
            $table->integer('totalSesiones');
            $table->double('precio');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paquetes_servicios');
    }
};
