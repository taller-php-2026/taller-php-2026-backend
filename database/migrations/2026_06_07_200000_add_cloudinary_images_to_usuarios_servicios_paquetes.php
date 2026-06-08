<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->text('imagenPerfilUrl')->nullable()->after('activo');
            $table->string('imagenPerfilPublicId')->nullable()->after('imagenPerfilUrl');
        });

        Schema::table('servicios', function (Blueprint $table) {
            $table->text('imagenUrl')->nullable()->after('activo');
            $table->string('imagenPublicId')->nullable()->after('imagenUrl');
        });

        Schema::table('paquetes_servicios', function (Blueprint $table) {
            $table->text('imagenUrl')->nullable()->after('activo');
            $table->string('imagenPublicId')->nullable()->after('imagenUrl');
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn(['imagenPerfilUrl', 'imagenPerfilPublicId']);
        });

        Schema::table('servicios', function (Blueprint $table) {
            $table->dropColumn(['imagenUrl', 'imagenPublicId']);
        });

        Schema::table('paquetes_servicios', function (Blueprint $table) {
            $table->dropColumn(['imagenUrl', 'imagenPublicId']);
        });
    }
};
