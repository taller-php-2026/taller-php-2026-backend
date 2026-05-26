<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReglaDisponibilidad;

class ReglaDisponibilidadController extends Controller
{
        public function index()
    {
        return ReglaDisponibilidad::all();
    }

    public function store(Request $request)
    {
        $reglaDisponibilidad = ReglaDisponibilidad::create($request->all());

        return response()->json($reglaDisponibilidad, 201);
    }

    public function show($id)
    {
        return ReglaDisponibilidad::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $reglaDisponibilidad = ReglaDisponibilidad::findOrFail($id);

        $reglaDisponibilidad->update($request->all());

        return response()->json($reglaDisponibilidad, 200);
    }

    public function destroy($id)
    {
        ReglaDisponibilidad::destroy($id);

        return response()->json([
            'mensaje' => 'Regla de disponibilidad eliminada correctamente'
        ], 200);
    }
}
