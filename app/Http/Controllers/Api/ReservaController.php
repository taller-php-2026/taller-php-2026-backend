<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reserva;

class ReservaController extends Controller
{
        public function index()
    {
        return Reserva::all();
    }

    public function store(Request $request)
    {
        $reserva = Reserva::create($request->all());

        return response()->json($reserva, 201);
    }

    public function show($id)
    {
        return Reserva::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $reserva = Reserva::findOrFail($id);

        $reserva->update($request->all());

        return response()->json($reserva, 200);
    }

    public function destroy($id)
    {
        Reserva::destroy($id);

        return response()->json([
            'mensaje' => 'Reserva eliminada correctamente'
        ], 200);
    }
}
