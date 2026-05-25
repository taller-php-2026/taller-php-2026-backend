<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use illuminate\Support\Facades\DB;

class AgendasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('agendas')->insert([
            [
                'idCiclo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'idCiclo' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
