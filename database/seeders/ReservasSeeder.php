<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use illuminate\Support\Facades\DB;

class ReservasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('reservas')->insert([
            [
                'fechaReserva' => now(),
                'estado' => 'confirmada',
                'comentarios' => 'Primera consulta',
                'idPago' => 1,
                'idProfesional' => 3,
                'idCliente' => 2,
                'idServicio' => 1,
                'idHorario' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
