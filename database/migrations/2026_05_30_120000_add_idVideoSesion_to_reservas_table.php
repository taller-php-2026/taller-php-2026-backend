<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->unsignedBigInteger('idVideoSesion')->nullable()->after('idPaqueteComprado');
            $table->foreign('idVideoSesion')->references('idVideoSesion')->on('video_sesiones')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropForeign(['idVideoSesion']);
            $table->dropColumn('idVideoSesion');
        });
    }
};
