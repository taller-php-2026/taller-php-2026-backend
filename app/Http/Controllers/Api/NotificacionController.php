<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notificacion;

class NotificacionController extends Controller
{
        public function index()
    {
        return Notificacion::all();
    }

    public function store(Request $request)
    {
        $notificacion = Notificacion::create($request->all());

        return response()->json($notificacion, 201);
    }

    public function show(int $id)
    {
        return Notificacion::findOrFail($id);
    }

    public function update(Request $request, int $id)
    {
        $notificacion = Notificacion::findOrFail($id);

        $notificacion->update($request->all());

        return response()->json($notificacion, 200);
    }

    public function destroy(int $id)
    {
        Notificacion::destroy($id);

        return response()->json([
            'mensaje' => 'Notificación eliminada correctamente'
        ], 200);
    }
}
