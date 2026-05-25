<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use illuminate\Support\Facades\DB;

class VideoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('video_sesiones')->insert([
        [
            'proveedor' => 'Zoom',
            'url' => 'https://zoom.us/j/123456789',
            'nombreSala' => 'Sesion Psicologia 1',
            'fechaHoraInicio' => now()->addDay(),
            'fechaHoraFin' => now()->addDay()->addHour(),
            'estado' => 'programada',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'proveedor' => 'Google Meet',
            'url' => 'https://meet.google.com/abc-defg-hij',
            'nombreSala' => 'Consulta Nutricion',
            'fechaHoraInicio' => now()->addDays(2),
            'fechaHoraFin' => now()->addDays(2)->addHour(),
            'estado' => 'programada',
            'created_at' => now(),
            'updated_at' => now(),
        ]
        ]);
    }
}
