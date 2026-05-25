<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use illuminate\Support\Facades\DB;

class AdministradoresSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('administradores')->insert([
        [
            'idUsuario' => 1,
            'nivelAcceso' => 'superadmin',
            'created_at' => now(),
            'updated_at' => now(),
        ]
    ]);
    }
}
