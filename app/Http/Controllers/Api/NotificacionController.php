<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notificacion;

class NotificacionController extends Controller
{
    private function queryDelUsuario(Request $request)
    {
        return Notificacion::where('idUsuario', $request->user()->idUsuario);
    }

    public function misNotificaciones(Request $request)
    {
        $query = $this->queryDelUsuario($request);

        return response()->json([
            'data' => (clone $query)
                ->orderBy('fechaCreacion', 'desc')
                ->limit(50)
                ->get(),
            'unreadCount' => (clone $query)->where('leida', false)->count(),
        ]);
    }

    public function marcarComoLeida(Request $request, int $id)
    {
        $notificacion = $this->queryDelUsuario($request)->findOrFail($id);

        $notificacion->update([
            'leida' => true,
            'fechaLectura' => $notificacion->fechaLectura ?? now(),
        ]);

        return response()->json([
            'data' => $notificacion->fresh(),
            'unreadCount' => $this->queryDelUsuario($request)->where('leida', false)->count(),
        ]);
    }

    public function marcarTodasComoLeidas(Request $request)
    {
        $this->queryDelUsuario($request)
            ->where('leida', false)
            ->update([
                'leida' => true,
                'fechaLectura' => now(),
            ]);

        return response()->json([
            'data' => $this->queryDelUsuario($request)
                ->orderBy('fechaCreacion', 'desc')
                ->limit(50)
                ->get(),
            'unreadCount' => 0,
        ]);
    }

    public function index(Request $request)
    {
        return $this->queryDelUsuario($request)
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
        return $this->queryDelUsuario($request)->findOrFail($id);
    }

    public function update(Request $request, int $id)
    {
        $notificacion = $this->queryDelUsuario($request)->findOrFail($id);

        $notificacion->update($request->all());

        return response()->json($notificacion, 200);
    }

    public function destroy(Request $request, int $id)
    {
        $notificacion = $this->queryDelUsuario($request)->findOrFail($id);
            
        $notificacion->delete();

        return response()->json([
            'mensaje' => 'Notificación eliminada correctamente'
        ], 200);
    }

    public function destroyAll(Request $request)
    {
        $this->queryDelUsuario($request)->delete();

        return response()->json([
            'mensaje' => 'Todas las notificaciones fueron eliminadas correctamente'
        ], 200);
    }
}
