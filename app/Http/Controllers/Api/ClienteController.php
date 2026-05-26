<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cliente;

class ClienteController extends Controller
{
        public function index()
    {
        return Cliente::all();
    }

    public function store(Request $request)
    {
        $cliente = Cliente::create($request->all());

        return response()->json($cliente, 201);
    }

    public function show($id)
    {
        return Cliente::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $cliente = Cliente::findOrFail($id);

        $cliente->update($request->all());

        return response()->json($cliente, 200);
    }

    public function destroy($id)
    {
        Cliente::destroy($id);

        return response()->json([
            'mensaje' => 'Cliente eliminado correctamente'
        ], 200);
    }
}
