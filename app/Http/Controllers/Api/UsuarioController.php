<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;
class UsuarioController extends Controller
{
        public function index()
    {
        return Usuario::all();
    }

    public function store(Request $request)
    {
        $usuario = Usuario::create($request->all());

        return response()->json($usuario, 201);
    }

    public function show($id)
    {
        return Usuario::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);

        $usuario->update($request->all());

        return response()->json($usuario, 200);
    }

    public function destroy($id)
    {
        Usuario::destroy($id);

        return response()->json([
            'mensaje' => 'Usuario eliminado correctamente'
        ], 200);
    }
}
