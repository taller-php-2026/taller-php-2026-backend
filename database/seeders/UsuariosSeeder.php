<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuariosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('usuarios')->insert([
        [
            'nombre' => 'Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('123456'),
            'telefono' => '099951611',
            'activo' => true,
            'fechaRegistro' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'nombre' => 'Usuario Prueba',
            'email' => 'usuario@test.com',
            'password' => Hash::make('123456'),
            'telefono' => '099951612',
            'activo' => true,
            'fechaRegistro' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'nombre' => 'Profesional 1',
            'email' => 'pro1@test.com',
            'password' => Hash::make('123456'),
            'telefono' => '099951613',
            'activo' => true,
            'fechaRegistro' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'nombre' => 'Profesional 2',
            'email' => 'pro2@test.com',
            'password' => Hash::make('123456'),
            'telefono' => '099951614',
            'activo' => true,
            'fechaRegistro' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]
    ]);
    }
}
