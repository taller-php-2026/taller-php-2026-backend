<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use illuminate\Support\Facades\DB;

class ExcepcionesdisponibilidadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('excepciones_disponibilidad')->insert([
            [
                'fecha' => '2026-05-30',
                'horaInicio' => '10:00:00',
                'horaFin' => '12:00:00',
                'motivo' => 'Capacitación',
                'idAgenda' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
