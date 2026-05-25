<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use illuminate\Support\Facades\DB;

class UbicacionesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('ubicaciones')->insert([
        [
            'direccion' => 'Av. 18 de Julio 1234',
            'ciudad' => 'Montevideo',
            'pais' => 'Uruguay',
            'latitud' => -349052,
            'longitud' => -561915,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'direccion' => 'Calle Principal 456',
            'ciudad' => 'Buenos Aires',
            'pais' => 'Argentina',
            'latitud' => -346037,
            'longitud' => -583816,
            'created_at' => now(),
            'updated_at' => now(),
        ]
        ]);
    }
}
