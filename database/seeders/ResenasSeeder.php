<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use illuminate\Support\Facades\DB;

class ResenasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('resenas')->insert([
            [
                'calificacion' => 5,
                'comentario' => 'Excelente atención.',
                'fecha' => now(),
                'idProfesional' => 3,
                'idCliente' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
