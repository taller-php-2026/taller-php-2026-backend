<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PagosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('pagos')->insert([
            [
                'monto' => 1500,
                'metodoPago' => 'Tarjeta',
                'estado' => 'aprobado',
                'fechaPago' => now(),
                'referenciaExterna' => 'PAY123',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'monto' => 2300,
                'metodoPago' => 'Transferencia',
                'estado' => 'pendiente',
                'fechaPago' => null,
                'referenciaExterna' => 'PAY124',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
