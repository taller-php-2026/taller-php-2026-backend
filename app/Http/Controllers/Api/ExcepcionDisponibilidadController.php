<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ExcepcionDisponibilidad;

class ExcepcionDisponibilidadController extends Controller
{
        public function index()
    {
        return ExcepcionDisponibilidad::all();
    }

    public function store(Request $request)
    {
        $excepcion = ExcepcionDisponibilidad::create($request->all());

        return response()->json($excepcion, 201);
    }

    public function show($id)
    {
        return ExcepcionDisponibilidad::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $excepcion = ExcepcionDisponibilidad::findOrFail($id);

        $excepcion->update($request->all());

        return response()->json($excepcion, 200);
    }

    public function destroy($id)
    {
        ExcepcionDisponibilidad::destroy($id);

        return response()->json([
            'mensaje' => 'Excepción de disponibilidad eliminada correctamente'
        ], 200);
    }
}
