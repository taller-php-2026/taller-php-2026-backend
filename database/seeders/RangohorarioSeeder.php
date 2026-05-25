<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use illuminate\Support\Facades\DB;

class RangohorarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('rango_horarios')->insert([
            [
                'diaSemana' => 'Lunes',
                'horaInicio' => '08:00:00',
                'horaFin' => '12:00:00',
                'idCiclo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'diaSemana' => 'Martes',
                'horaInicio' => '14:00:00',
                'horaFin' => '18:00:00',
                'idCiclo' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
