<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Resena;

class ResenaController extends Controller
{
    // Obtener todas las reseñas filtradas por servicio o profesional.
    public function index(Request $request)
    {
        $idServicio = $request->query('idServicio');
        if ($idServicio) {
            return Resena::with('cliente.usuario')
                ->whereHas('reserva', function ($q) use ($idServicio) {
                    $q->where('idServicio', $idServicio);
                })
                ->get();
        }

        $idProfesional = $request->query('idProfesional');
        if ($idProfesional) {
            return Resena::with(['cliente.usuario', 'reserva.servicio'])
                ->where('idProfesional', $idProfesional)
                ->get();
        }

        return Resena::all();
    }

    public function store(Request $request)
    {
        $resena = Resena::create($request->all());

        return response()->json($resena, 201);
    }

    public function show($id)
    {
        return Resena::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $resena = Resena::findOrFail($id);

        $resena->update($request->all());

        return response()->json($resena, 200);
    }

    public function destroy($id)
    {
        Resena::destroy($id);

        return response()->json([
            'mensaje' => 'Reseña eliminada correctamente'
        ], 200);
    }
}
