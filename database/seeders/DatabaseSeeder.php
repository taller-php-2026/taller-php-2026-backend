<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── 1. UBICACIONES ───────────────────────────────────────────────
        $ubicaciones = [
            ['direccion' => 'Av. 18 de Julio 1234', 'ciudad' => 'Montevideo', 'pais' => 'Uruguay', 'latitud' => -34, 'longitud' => -56],
            ['direccion' => 'Bulevar Artigas 567',  'ciudad' => 'Montevideo', 'pais' => 'Uruguay', 'latitud' => -34, 'longitud' => -56],
            ['direccion' => 'Av. Italia 890',        'ciudad' => 'Montevideo', 'pais' => 'Uruguay', 'latitud' => -34, 'longitud' => -56],
        ];
        foreach ($ubicaciones as $u) {
            DB::table('ubicaciones')->insert(array_merge($u, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // ─── 2. USUARIOS (3 clientes + 3 profesionales) ───────────────────
        $usuariosData = [
            // clientes
            ['nombre' => 'Ana García',      'email' => 'ana@mail.com',      'telefono' => '091111111'],
            ['nombre' => 'Carlos López',    'email' => 'carlos@mail.com',   'telefono' => '092222222'],
            ['nombre' => 'María Fernández', 'email' => 'maria@mail.com',    'telefono' => '093333333'],
            // profesionales
            ['nombre' => 'Dr. Pablo Ruiz',  'email' => 'pablo@mail.com',    'telefono' => '094444444'],
            ['nombre' => 'Lic. Sofía Mora', 'email' => 'sofia@mail.com',    'telefono' => '095555555'],
            ['nombre' => 'Dr. Lucas Pérez', 'email' => 'lucas@mail.com',    'telefono' => '096666666'],
        ];

        $usuarioIds = [];
        foreach ($usuariosData as $u) {
            // Le pasamos 'idUsuario' como segundo parámetro para decirle a Postgres qué retornar
            $id = DB::table('usuarios')->insertGetId(array_merge($u, [
                'password'      => Hash::make('password123'),
                'activo'        => 1,
                'fechaRegistro' => now(),
                'created_at'    => now(),
                'updated_at'    => now(),
            ]), 'idUsuario'); // <--- Acá está el truco
            
            $usuarioIds[] = $id;
        }

        [
            $idCliente1,
            $idCliente2,
            $idCliente3,
            $idProf1,
            $idProf2,
            $idProf3
        ] = $usuarioIds;

        // ─── 3. CLIENTES ──────────────────────────────────────────────────
        foreach ([$idCliente1, $idCliente2, $idCliente3] as $id) {
            DB::table('clientes')->insert([
                'idUsuario'  => $id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        

        // ─── 4. PROFESIONALES ─────────────────────────────────────────────
        $profData = [
            $idProf1 => ['nombreNegocio' => 'Clínica Ruiz',        'descripcion' => 'Médico clínico con 10 años de experiencia.',    'ratingPromedio' => 4.8],
            $idProf2 => ['nombreNegocio' => 'Psicología Mora',     'descripcion' => 'Psicóloga especializada en terapia cognitiva.', 'ratingPromedio' => 4.6],
            $idProf3 => ['nombreNegocio' => 'Consultorio Pérez',   'descripcion' => 'Nutricionista y coach de bienestar.',            'ratingPromedio' => 4.5],
        ];
        foreach ($profData as $idUsuario => $data) {
            DB::table('profesionales')->insert(array_merge($data, [
                'idUsuario'  => $idUsuario,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // ─── 5. SERVICIOS ─────────────────────────────────────────────────
        $servicios = [
            ['nombre' => 'Consulta médica general',  'descripcion' => 'Consulta clínica general presencial.',         'precio' => 800,  'duracionMinutos' => 30,  'modalidad' => 'presencial', 'idUbicacion' => 1],
            ['nombre' => 'Sesión de psicología',     'descripcion' => 'Sesión terapéutica individual online.',        'precio' => 1200, 'duracionMinutos' => 60,  'modalidad' => 'virtual',     'idUbicacion' => null],
            ['nombre' => 'Consulta nutricional',     'descripcion' => 'Plan nutricional personalizado presencial.',   'precio' => 950,  'duracionMinutos' => 45,  'modalidad' => 'presencial', 'idUbicacion' => 2],
            ['nombre' => 'Control de seguimiento',   'descripcion' => 'Control post-consulta de 15 minutos.',        'precio' => 400,  'duracionMinutos' => 15,  'modalidad' => 'presencial', 'idUbicacion' => 1],
            ['nombre' => 'Terapia online',           'descripcion' => 'Sesión de terapia por videollamada.',          'precio' => 1100, 'duracionMinutos' => 50,  'modalidad' => 'virtual',     'idUbicacion' => null],
        ];
        foreach ($servicios as $s) {
            DB::table('servicios')->insert(array_merge($s, [
                'activo'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // ─── 6. PAGOS ─────────────────────────────────────────────────────
        $pagos = [
            ['monto' => 800,  'metodoPago' => 'tarjeta',      'estado' => 'aprobado', 'fechaPago' => Carbon::now()->subDays(10), 'referenciaExterna' => 'REF-001'],
            ['monto' => 1200, 'metodoPago' => 'transferencia', 'estado' => 'aprobado', 'fechaPago' => Carbon::now()->subDays(8),  'referenciaExterna' => 'REF-002'],
            ['monto' => 950,  'metodoPago' => 'efectivo',     'estado' => 'aprobado', 'fechaPago' => Carbon::now()->subDays(5),  'referenciaExterna' => null],
            ['monto' => 400,  'metodoPago' => 'tarjeta',      'estado' => 'pendiente',  'fechaPago' => null,                       'referenciaExterna' => null],
            ['monto' => 1100, 'metodoPago' => 'transferencia', 'estado' => 'aprobado', 'fechaPago' => Carbon::now()->subDays(2),  'referenciaExterna' => 'REF-005'],
        ];
        foreach ($pagos as $p) {
            DB::table('pagos')->insert(array_merge($p, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // ─── 7. HORARIOS (necesarios para reservas) ───────────────────────
        // Insertar horarios
        $horarioIds = [];
        $horariosData = [
            ['fecha' => Carbon::now()->subDays(10)->toDateString(), 'horaInicio' => '09:00', 'horaFin' => '09:30'],
            ['fecha' => Carbon::now()->subDays(8)->toDateString(),  'horaInicio' => '10:00', 'horaFin' => '11:00'],
            ['fecha' => Carbon::now()->subDays(5)->toDateString(),  'horaInicio' => '14:00', 'horaFin' => '14:45'],
            ['fecha' => Carbon::now()->addDays(2)->toDateString(),  'horaInicio' => '11:00', 'horaFin' => '11:15'],
            ['fecha' => Carbon::now()->subDays(2)->toDateString(),  'horaInicio' => '15:00', 'horaFin' => '15:50'],
        ];
        // Cambiá el insert de horarios pasándole 'idHorario' al final:
        foreach ($horariosData as $h) {
            $horarioIds[] = DB::table('horarios')->insertGetId(array_merge($h, [
                'created_at' => now(),
                'updated_at' => now(),
            ]), 'idHorario'); // <--- Clave primaria real
        }

        // ─── 8. RESERVAS ──────────────────────────────────────────────────
        $reservas = [
            ['fechaReserva' => Carbon::now()->subDays(10), 'estado' => 'confirmada',  'comentarios' => 'Primera consulta.',    'idPago' => 1, 'idProfesional' => $idProf1, 'idCliente' => $idCliente1, 'idServicio' => 1, 'idHorario' => $horarioIds[0]],
            ['fechaReserva' => Carbon::now()->subDays(8),  'estado' => 'confirmada',  'comentarios' => 'Sesión de ansiedad.',  'idPago' => 2, 'idProfesional' => $idProf2, 'idCliente' => $idCliente2, 'idServicio' => 2, 'idHorario' => $horarioIds[1]],
            ['fechaReserva' => Carbon::now()->subDays(5),  'estado' => 'confirmada',  'comentarios' => null,                   'idPago' => 3, 'idProfesional' => $idProf3, 'idCliente' => $idCliente3, 'idServicio' => 3, 'idHorario' => $horarioIds[2]],
            ['fechaReserva' => Carbon::now()->addDays(2),  'estado' => 'pendiente',   'comentarios' => 'Control mensual.',     'idPago' => 4, 'idProfesional' => $idProf1, 'idCliente' => $idCliente2, 'idServicio' => 4, 'idHorario' => $horarioIds[3]],
            ['fechaReserva' => Carbon::now()->subDays(2),  'estado' => 'confirmada',  'comentarios' => 'Terapia online.',      'idPago' => 5, 'idProfesional' => $idProf2, 'idCliente' => $idCliente1, 'idServicio' => 5, 'idHorario' => $horarioIds[4]],
        ];
        foreach ($reservas as $r) {
            DB::table('reservas')->insert(array_merge($r, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // ─── 9. RESEÑAS ───────────────────────────────────────────────────
        $resenas = [
            ['calificacion' => 5, 'comentario' => 'Excelente atención, muy profesional.',     'fecha' => Carbon::now()->subDays(9)->toDateString(),  'idProfesional' => $idProf1, 'idCliente' => $idCliente1, 'idReserva' => 1],
            ['calificacion' => 4, 'comentario' => 'Muy buena sesión, me sentí escuchada.',    'fecha' => Carbon::now()->subDays(7)->toDateString(),  'idProfesional' => $idProf2, 'idCliente' => $idCliente2, 'idReserva' => 2],
            ['calificacion' => 5, 'comentario' => 'El plan nutricional fue muy completo.',    'fecha' => Carbon::now()->subDays(4)->toDateString(),  'idProfesional' => $idProf3, 'idCliente' => $idCliente3, 'idReserva' => 3],
            ['calificacion' => 4, 'comentario' => 'Buena sesión online, sin problemas técnicos.', 'fecha' => Carbon::now()->subDays(1)->toDateString(), 'idProfesional' => $idProf2, 'idCliente' => $idCliente1, 'idReserva' => 5],
        ];
        foreach ($resenas as $r) {
            DB::table('resenas')->insert(array_merge($r, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // ─── 10. NOTIFICACIONES ───────────────────────────────────────────
        $notificaciones = [
            ['idUsuario' => $idCliente1, 'titulo' => 'Reserva confirmada', 'mensaje' => 'Tu reserva fue confirmada para mañana.', 'tipo' => 'confirmacion',  'leida' => 0, 'enviadaMail' => 0, 'fechaCreacion' => now(), 'fechaLectura' => null],
            ['idUsuario' => $idCliente2, 'titulo' => 'Recordatorio',       'mensaje' => 'Recordatorio: consulta en 2 dias.',       'tipo' => 'recordatorio',  'leida' => 0, 'enviadaMail' => 1, 'fechaCreacion' => now(), 'fechaLectura' => null],
            ['idUsuario' => $idProf1,    'titulo' => 'Nueva reserva',      'mensaje' => 'Nueva reserva recibida.',                  'tipo' => 'actualizacion', 'leida' => 1, 'enviadaMail' => 0, 'fechaCreacion' => now(), 'fechaLectura' => now()],
            ['idUsuario' => $idCliente3, 'titulo' => 'Pago procesado',     'mensaje' => 'Tu pago fue procesado correctamente.',     'tipo' => 'confirmacion',  'leida' => 1, 'enviadaMail' => 1, 'fechaCreacion' => now(), 'fechaLectura' => now()],
        ];
        foreach ($notificaciones as $n) {
            DB::table('notificaciones')->insert(array_merge($n, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        DB::table('profesionales_servicios')->insert([
            ['idProfesional' => $idProf1, 'idServicio' => 1],
            ['idProfesional' => $idProf1, 'idServicio' => 4],
            ['idProfesional' => $idProf2, 'idServicio' => 2],
            ['idProfesional' => $idProf2, 'idServicio' => 5],
            ['idProfesional' => $idProf3, 'idServicio' => 3],
            ['idProfesional' => $idProf2, 'idServicio' => 3],
        ]);

        // ─── CICLOS ───────────────────────────────────────────────────────────
        $ciclo1 = DB::table('ciclos')->insertGetId(['nombre' => 'Semana estándar', 'created_at' => now(), 'updated_at' => now()], 'idCiclo');
        $ciclo2 = DB::table('ciclos')->insertGetId(['nombre' => 'Semana reducida', 'created_at' => now(), 'updated_at' => now()], 'idCiclo');

        // ─── RANGO HORARIOS ───────────────────────────────────────────────────
        $rangos = [
            ['idCiclo' => $ciclo1, 'diaSemana' => 'Lunes',    'horaInicio' => '09:00', 'horaFin' => '17:00'],
            ['idCiclo' => $ciclo1, 'diaSemana' => 'Martes',   'horaInicio' => '09:00', 'horaFin' => '17:00'],
            ['idCiclo' => $ciclo1, 'diaSemana' => 'Miércoles', 'horaInicio' => '09:00', 'horaFin' => '17:00'],
            ['idCiclo' => $ciclo1, 'diaSemana' => 'Jueves',   'horaInicio' => '09:00', 'horaFin' => '17:00'],
            ['idCiclo' => $ciclo1, 'diaSemana' => 'Viernes',  'horaInicio' => '09:00', 'horaFin' => '13:00'],
            ['idCiclo' => $ciclo2, 'diaSemana' => 'Lunes',    'horaInicio' => '10:00', 'horaFin' => '14:00'],
            ['idCiclo' => $ciclo2, 'diaSemana' => 'Miércoles', 'horaInicio' => '10:00', 'horaFin' => '14:00'],
        ];
        foreach ($rangos as $r) {
            DB::table('rango_horarios')->insert(array_merge($r, ['created_at' => now(), 'updated_at' => now()]));
        }

        // ─── AGENDAS ──────────────────────────────────────────────────────────
        $agenda1 = DB::table('agendas')->insertGetId(['idCiclo' => $ciclo1, 'created_at' => now(), 'updated_at' => now()], 'idAgenda');
        $agenda2 = DB::table('agendas')->insertGetId(['idCiclo' => $ciclo1, 'created_at' => now(), 'updated_at' => now()], 'idAgenda');
        $agenda3 = DB::table('agendas')->insertGetId(['idCiclo' => $ciclo2, 'created_at' => now(), 'updated_at' => now()], 'idAgenda');

        // ─── REGLAS DISPONIBILIDAD ────────────────────────────────────────────
        $reglas = [
            ['idProfesional' => $idProf1, 'idAgenda' => $agenda1, 'dia_semana' => 'Lunes',    'horaInicio' => '09:00', 'horaFin' => '17:00', 'pausaMinutos' => 15, 'bufferMinutos' => 10, 'activa' => 1],
            ['idProfesional' => $idProf1, 'idAgenda' => $agenda1, 'dia_semana' => 'Martes',   'horaInicio' => '09:00', 'horaFin' => '17:00', 'pausaMinutos' => 15, 'bufferMinutos' => 10, 'activa' => 1],
            ['idProfesional' => $idProf1, 'idAgenda' => $agenda1, 'dia_semana' => 'Miércoles', 'horaInicio' => '09:00', 'horaFin' => '17:00', 'pausaMinutos' => 15, 'bufferMinutos' => 10, 'activa' => 1],
            ['idProfesional' => $idProf2, 'idAgenda' => $agenda2, 'dia_semana' => 'Lunes',    'horaInicio' => '10:00', 'horaFin' => '18:00', 'pausaMinutos' => 20, 'bufferMinutos' => 5,  'activa' => 1],
            ['idProfesional' => $idProf2, 'idAgenda' => $agenda2, 'dia_semana' => 'Jueves',   'horaInicio' => '10:00', 'horaFin' => '18:00', 'pausaMinutos' => 20, 'bufferMinutos' => 5,  'activa' => 1],
            ['idProfesional' => $idProf3, 'idAgenda' => $agenda3, 'dia_semana' => 'Miércoles', 'horaInicio' => '08:00', 'horaFin' => '14:00', 'pausaMinutos' => 10, 'bufferMinutos' => 0,  'activa' => 1],
            ['idProfesional' => $idProf3, 'idAgenda' => $agenda3, 'dia_semana' => 'Viernes',  'horaInicio' => '08:00', 'horaFin' => '14:00', 'pausaMinutos' => 10, 'bufferMinutos' => 0,  'activa' => 1],
        ];
        foreach ($reglas as $r) {
            DB::table('reglas_disponibilidad')->insert(array_merge($r, ['created_at' => now(), 'updated_at' => now()]));
        }
    }
}
