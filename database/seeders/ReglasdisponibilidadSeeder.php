<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use illuminate\Support\Facades\DB;

class ReglasdisponibilidadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('reglas_disponibilidad')->insert([
            [
                'dia_semana' => 'Lunes',
                'horaInicio' => '08:00:00',
                'horaFin' => '12:00:00',
                'pausaMinutos' => 15,
                'bufferMinutos' => 10,
                'activa' => true,
                'idAgenda' => 1,
                'idProfesional' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
