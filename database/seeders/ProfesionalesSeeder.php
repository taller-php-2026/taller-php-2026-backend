<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProfesionalesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('profesionales')->insert([
    [
        'idUsuario' => 3,
        'nombreNegocio' => 'Barbería Elite',
        'descripcion' => 'Especialistas en cortes modernos.',
        'ratingPromedio' => 4.8,
        'created_at' => now(),
        'updated_at' => now(),
    ],
    [
        'idUsuario' => 4,
        'nombreNegocio' => 'Spa Relax',
        'descripcion' => 'Masajes y tratamientos.',
        'ratingPromedio' => 4.5,
        'created_at' => now(),
        'updated_at' => now(),
    ]
]);
    }
}
