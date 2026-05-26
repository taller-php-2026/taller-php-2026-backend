<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pago;

class PagoController extends Controller
{
        public function index()
    {
        return Pago::all();
    }

    public function store(Request $request)
    {
        $pago = Pago::create($request->all());

        return response()->json($pago, 201);
    }

    public function show($id)
    {
        return Pago::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $pago = Pago::findOrFail($id);

        $pago->update($request->all());

        return response()->json($pago, 200);
    }

    public function destroy($id)
    {
        Pago::destroy($id);

        return response()->json([
            'mensaje' => 'Pago eliminado correctamente'
        ], 200);
    }
}
