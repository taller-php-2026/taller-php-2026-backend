<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProfesionalesserviciosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('profesionales_servicios')->insert([
            [
                'idProfesional' => 3,
                'idServicio' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
