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
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id('idNotificacion');
            $table->string('titulo');
            $table->text('mensaje');
            $table->enum('tipo', ['confirmacion', 'recordatorio', 'cancelacion', 'actualizacion', 'mensaje']);
            $table->boolean('leida')->default(false);
            $table->boolean('enviadaMail')->default(false);
            $table->dateTime('fechaCreacion');
            $table->dateTime('fechaLectura')->nullable();
            $table->foreignId('idUsuario')->constrained('usuarios')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
