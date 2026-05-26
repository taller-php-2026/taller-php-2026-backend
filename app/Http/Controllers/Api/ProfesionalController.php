<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Profesional;

class ProfesionalController extends Controller
{
        public function index()
    {
        return Profesional::all();
    }

    public function store(Request $request)
    {
        $profesional = Profesional::create($request->all());

        return response()->json($profesional, 201);
    }

    public function show($id)
    {
        return Profesional::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $profesional = Profesional::findOrFail($id);

        $profesional->update($request->all());

        return response()->json($profesional, 200);
    }

    public function destroy($id)
    {
        Profesional::destroy($id);

        return response()->json([
            'mensaje' => 'Profesional eliminado correctamente'
        ], 200);
    }
}
