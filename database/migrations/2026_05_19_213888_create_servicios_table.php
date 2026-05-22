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
        Schema::create('servicios', function (Blueprint $table) {
            $table->id('idServicio');
            $table->string('nombre');
            $table->text('descripcion');
            $table->double('precio');
            $table->integer('duracionMinutos');
            $table->boolean('activo')->default(true);
            $table->enum('modalidad', ['presencial', 'virtual', 'hibrida'])->default('presencial');
            $table->unsignedBigInteger('idUbicacion')->nullable();
            $table->foreign('idUbicacion')->references('idUbicacion')->on('ubicaciones')->onDelete('cascade');
            $table->unsignedBigInteger('idVideoSesion')->nullable();
            $table->foreign('idVideoSesion')->references('idVideoSesion')->on('video_sesiones')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servicios');
    }
};
