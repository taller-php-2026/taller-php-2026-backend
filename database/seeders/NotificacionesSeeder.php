<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificacionesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('notificaciones')->insert([
            [
                'titulo' => 'Bienvenido',
                'mensaje' => 'Tu cuenta fue creada correctamente.',
                'tipo' => 'confirmacion',
                'leida' => false,
                'enviadaMail' => true,
                'fechaCreacion' => now(),
                'fechaLectura' => null,
                'idUsuario' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'titulo' => 'Recordatorio',
                'mensaje' => 'Tienes una sesión programada mañana.',
                'tipo' => 'recordatorio',
                'leida' => false,
                'enviadaMail' => false,
                'fechaCreacion' => now(),
                'fechaLectura' => null,
                'idUsuario' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
