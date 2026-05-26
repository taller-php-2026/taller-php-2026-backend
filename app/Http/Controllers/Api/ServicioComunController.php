<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServicioComun;

class ServicioComunController extends Controller
{
        public function index()
    {
        return ServicioComun::all();
    }

    public function store(Request $request)
    {
        $servicioComun = ServicioComun::create($request->all());

        return response()->json($servicioComun, 201);
    }

    public function show($id)
    {
        return ServicioComun::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $servicioComun = ServicioComun::findOrFail($id);

        $servicioComun->update($request->all());

        return response()->json($servicioComun, 200);
    }

    public function destroy($id)
    {
        ServicioComun::destroy($id);

        return response()->json([
            'mensaje' => 'Servicio comun eliminado correctamente'
        ], 200);
    }
}
