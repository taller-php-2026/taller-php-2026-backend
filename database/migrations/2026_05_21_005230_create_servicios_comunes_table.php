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
        Schema::create('servicios_comunes', function (Blueprint $table) {
            $table->id('idServicioComun');
            $table->unsignedBigInteger('idServicio')->unique();
            $table->foreign('idServicio')->references('idServicio')->on('servicios')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servicios_comunes');
    }
};
