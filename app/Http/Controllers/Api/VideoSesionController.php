<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VideoSesion;

class VideoSesionController extends Controller
{
        public function index()
    {
        return VideoSesion::all();
    }

    public function store(Request $request)
    {
        $videoSesion = VideoSesion::create($request->all());

        return response()->json($videoSesion, 201);
    }

    public function show($id)
    {
        return VideoSesion::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $videoSesion = VideoSesion::findOrFail($id);

        $videoSesion->update($request->all());

        return response()->json($videoSesion, 200);
    }

    public function destroy($id)
    {
        VideoSesion::destroy($id);

        return response()->json([
            'mensaje' => 'VideoSesion eliminada correctamente'
        ], 200);
    }
}
