<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use illuminate\Support\Facades\DB;

class ServiciosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('servicios')->insert([
            [
                'nombre' => 'Consulta Psicológica',
                'descripcion' => 'Sesión individual de apoyo psicológico.',
                'precio' => 1500,
                'duracionMinutos' => 60,
                'activo' => true,
                'modalidad' => 'presencial',
                'idUbicacion' => 1,
                'idVideoSesion' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Consulta Virtual',
                'descripcion' => 'Sesión online mediante videollamada.',
                'precio' => 1200,
                'duracionMinutos' => 45,
                'activo' => true,
                'modalidad' => 'virtual',
                'idUbicacion' => null,
                'idVideoSesion' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Terapia Híbrida',
                'descripcion' => 'Servicio con modalidad presencial y virtual.',
                'precio' => 1800,
                'duracionMinutos' => 90,
                'activo' => true,
                'modalidad' => 'hibrida',
                'idUbicacion' => 2,
                'idVideoSesion' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
