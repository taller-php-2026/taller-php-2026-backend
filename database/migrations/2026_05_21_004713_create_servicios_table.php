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
            $table->foreignId('idUbicacion')->nullable()->constrained('ubicaciones')->onDelete('cascade');
            $table->foreignId('idVideoSesion')->nullable()->constrained('video_sesiones')->onDelete('set null');
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
