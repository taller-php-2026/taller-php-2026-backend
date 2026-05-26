<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Agenda;

class AgendaController extends Controller
{
    public function index()
    {
        return Agenda::all();
    }

    public function store(Request $request)
    {
        $agenda = Agenda::create($request->all());

        return response()->json($agenda, 201);
    }

    public function show($id)
    {
        return Agenda::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $agenda = Agenda::findOrFail($id);

        $agenda->update($request->all());

        return response()->json($agenda, 200);
    }

    public function destroy($id)
    {
        Agenda::destroy($id);

        return response()->json([
            'mensaje' => 'Agenda eliminada correctamente'
        ], 200);
    }
}