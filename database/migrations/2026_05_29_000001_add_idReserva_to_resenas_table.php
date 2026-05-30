<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resenas', function (Blueprint $table) {
            $table->unsignedBigInteger('idReserva')->nullable()->unique()->after('idCliente');
            $table->foreign('idReserva')->references('idReserva')->on('reservas')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('resenas', function (Blueprint $table) {
            $table->dropForeign(['idReserva']);
            $table->dropColumn('idReserva');
        });
    }
};
