<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaqueteServicio;

class PaqueteServicioController extends Controller
{
        public function index()
    {
        return PaqueteServicio::all();
    }

    public function store(Request $request)
    {
        $paqueteServicio = PaqueteServicio::create($request->all());

        return response()->json($paqueteServicio, 201);
    }

    public function show($id)
    {
        return PaqueteServicio::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $paqueteServicio = PaqueteServicio::findOrFail($id);

        $paqueteServicio->update($request->all());

        return response()->json($paqueteServicio, 200);
    }

    public function destroy($id)
    {
        PaqueteServicio::destroy($id);

        return response()->json([
            'mensaje' => 'Paquete de servicio eliminado correctamente'
        ], 200);
    }
}
