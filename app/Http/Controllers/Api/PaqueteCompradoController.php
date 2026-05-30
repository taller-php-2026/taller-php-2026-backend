<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PagarPaqueteCompradoRequest;
use App\Http\Requests\StorePaqueteCompradoRequest;
use App\Services\PaqueteCompradoService;

class PaqueteCompradoController extends Controller
{
    public function __construct(private PaqueteCompradoService $paqueteCompradoService) {}

    public function comprar(StorePaqueteCompradoRequest $request, $id)
    {
        $paqueteComprado = $this->paqueteCompradoService->comprar((int) $id, $request->validated());

        return response()->json([
            'message' => 'Compra de paquete creada correctamente',
            'data'    => [
                'paqueteComprado' => $paqueteComprado,
            ],
        ], 201);
    }

    public function pagar(PagarPaqueteCompradoRequest $request, $id)
    {
        $result = $this->paqueteCompradoService->pagar((int) $id, $request->validated());

        return response()->json([
            'message' => 'Paquete pagado correctamente',
            'data'    => $result,
        ]);
    }

    public function porCliente($id)
    {
        $paquetes = $this->paqueteCompradoService->getByCliente((int) $id);

        return response()->json([
            'data' => $paquetes,
        ]);
    }
}
