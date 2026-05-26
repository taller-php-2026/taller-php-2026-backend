<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Servicio;

class ServicioController extends Controller
{
        public function index()
    {
        return Servicio::all();
    }

    public function store(Request $request)
    {
        $servicio = Servicio::create($request->all());

        return response()->json($servicio, 201);
    }

    public function show($id)
    {
        return Servicio::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $servicio = Servicio::findOrFail($id);

        $servicio->update($request->all());

        return response()->json($servicio, 200);
    }

    public function destroy($id)
    {
        Servicio::destroy($id);

        return response()->json([
            'mensaje' => 'Servicio eliminado correctamente'
        ], 200);
    }
}
