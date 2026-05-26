<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ubicacion;

class UbicacionController extends Controller
{
        public function index()
    {
        return Ubicacion::all();
    }

    public function store(Request $request)
    {
        $ubicacion = Ubicacion::create($request->all());

        return response()->json($ubicacion, 201);
    }

    public function show($id)
    {
        return Ubicacion::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $ubicacion = Ubicacion::findOrFail($id);

        $ubicacion->update($request->all());

        return response()->json($ubicacion, 200);
    }

    public function destroy($id)
    {
        Ubicacion::destroy($id);

        return response()->json([
            'mensaje' => 'Ubicacion eliminada correctamente'
        ], 200);
    }
}
