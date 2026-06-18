<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! $this->constraintExists('notificaciones_idusuario_unique')) {
            return;
        }

        Schema::table('notificaciones', function (Blueprint $table) {
            $table->dropUnique(['idUsuario']);
        });
    }

    public function down(): void
    {
        if ($this->constraintExists('notificaciones_idusuario_unique')) {
            return;
        }

        Schema::table('notificaciones', function (Blueprint $table) {
            $table->unique('idUsuario');
        });
    }

    private function constraintExists(string $name): bool
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return true;
        }

        return DB::table('pg_constraint')->where('conname', $name)->exists();
    }
};
