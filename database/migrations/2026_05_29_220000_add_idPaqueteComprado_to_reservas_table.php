<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->unsignedBigInteger('idPaqueteComprado')->nullable()->after('idHorario');
            $table->foreign('idPaqueteComprado')
                  ->references('idPaqueteComprado')
                  ->on('paquetes_comprados')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropForeign(['idPaqueteComprado']);
            $table->dropColumn('idPaqueteComprado');
        });
    }
};
