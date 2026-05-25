<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HorariosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('horarios')->insert([
            [
                'fecha' => '2026-06-01',
                'horaInicio' => '09:00:00',
                'horaFin' => '10:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'fecha' => '2026-06-02',
                'horaInicio' => '14:00:00',
                'horaFin' => '15:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
