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
        Schema::create('paquetes_servicios_comunes', function (Blueprint $table) {
            $table->id('idPaqueteServicioComun');
            $table->unsignedBigInteger('idPaqueteServicio');
            $table->foreign('idPaqueteServicio')
                  ->references('idPaqueteServicio')
                  ->on('paquetes_servicios')
                  ->onDelete('cascade');

            $table->unsignedBigInteger('idServicioComun');
            $table->foreign('idServicioComun')
                  ->references('idServicioComun')
                  ->on('servicios_comunes')
                  ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paquetes_servicios_comunes');
    }
};
