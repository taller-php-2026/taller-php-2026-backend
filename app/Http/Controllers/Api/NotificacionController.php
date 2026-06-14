<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notificacion;

class NotificacionController extends Controller
{
    public function index(Request $request)
    {
        return Notificacion::where('idUsuario', $request->user()->idUsuario)
            ->orderBy('fechaCreacion', 'desc')
            ->get();
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $data['idUsuario'] = $request->user()->idUsuario;
        $notificacion = Notificacion::create($data);

        return response()->json($notificacion, 201);
    }

    public function show(Request $request, int $id)
    {
        return Notificacion::where('idUsuario', $request->user()->idUsuario)
            ->findOrFail($id);
    }

    public function update(Request $request, int $id)
    {
        $notificacion = Notificacion::where('idUsuario', $request->user()->idUsuario)
            ->findOrFail($id);

        $notificacion->update($request->all());

        return response()->json($notificacion, 200);
    }

    public function destroy(Request $request, int $id)
    {
        $notificacion = Notificacion::where('idUsuario', $request->user()->idUsuario)
            ->findOrFail($id);
            
        $notificacion->delete();

        return response()->json([
            'mensaje' => 'Notificación eliminada correctamente'
        ], 200);
    }

    public function destroyAll(Request $request)
    {
        Notificacion::where('idUsuario', $request->user()->idUsuario)->delete();

        return response()->json([
            'mensaje' => 'Todas las notificaciones fueron eliminadas correctamente'
        ], 200);
    }
}
