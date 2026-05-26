<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ciclo;

class CicloController extends Controller
{
        public function index()
    {
        return Ciclo::all();
    }

    public function store(Request $request)
    {
        $ciclo = Ciclo::create($request->all());

        return response()->json($ciclo, 201);
    }

    public function show($id)
    {
        return Ciclo::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $ciclo = Ciclo::findOrFail($id);

        $ciclo->update($request->all());

        return response()->json($ciclo, 200);
    }

    public function destroy($id)
    {
        Ciclo::destroy($id);

        return response()->json([
            'mensaje' => 'Ciclo eliminado correctamente'
        ], 200);
    }
}
