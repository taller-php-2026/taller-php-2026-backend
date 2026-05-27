<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePagoRequest;
use App\Http\Requests\UpdatePagoRequest;
use App\Services\PagoService;

class PagoController extends Controller
{
    public function __construct(private PagoService $pagoService) {}

    public function index()
    {
        $pagos = $this->pagoService->getAll();

        return response()->json([
            'data' => $pagos,
        ]);
    }

    public function store(StorePagoRequest $request)
    {
        $pago = $this->pagoService->create($request->validated());

        return response()->json([
            'message' => 'Pago creado correctamente',
            'data'    => $pago,
        ], 201);
    }

    public function show($id)
    {
        $pago = $this->pagoService->getById((int) $id);

        return response()->json([
            'data' => $pago,
        ]);
    }

    public function update(UpdatePagoRequest $request, $id)
    {
        $pago = $this->pagoService->getById((int) $id);
        $pago = $this->pagoService->update($pago, $request->validated());

        return response()->json([
            'message' => 'Pago actualizado correctamente',
            'data'    => $pago,
        ]);
    }

    public function destroy($id)
    {
        $pago = $this->pagoService->getById((int) $id);
        $this->pagoService->delete($pago);

        return response()->json([
            'message' => 'Pago eliminado correctamente',
        ]);
    }
}

