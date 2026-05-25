<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use illuminate\Support\Facades\DB;

class CiclosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('ciclos')->insert([
            [
                'nombre' => 'Ciclo Mañana',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Ciclo Tarde',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
