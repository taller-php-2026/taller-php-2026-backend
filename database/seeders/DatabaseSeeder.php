<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
       
        $this->call([
            UsuariosSeeder::class,
            AdministradoresSeeder::class,
            ClientesSeeder::class,
            ProfesionalesSeeder::class,
            NotificacionesSeeder::class,
            UbicacionesSeeder::class,
            VideoSeeder::class,
            CiclosSeeder::class,
            PagosSeeder::class,
            HorariosSeeder::class,
            AgendasSeeder::class,
            RangohorarioSeeder::class,
            ServiciosSeeder::class,
            ReglasDisponibilidadSeeder::class,
            ExcepcionesDisponibilidadSeeder::class,
            ProfesionalesServiciosSeeder::class,
            ServiciosComunesSeeder::class,
            PaquetesServiciosSeeder::class,
            ResenasSeeder::class,
            ReservasSeeder::class,
        ]);
    }
}
