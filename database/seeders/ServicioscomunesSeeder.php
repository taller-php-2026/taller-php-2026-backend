<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServicioscomunesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('servicios_comunes')->insert([
            [
                'idServicio' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
