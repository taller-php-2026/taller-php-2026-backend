<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Services\ClienteService;

class ClienteController extends Controller
{
    public function __construct(private ClienteService $clienteService) {}

    public function index()
    {
        $clientes = $this->clienteService->getAll();

        return response()->json([
            'data' => $clientes,
        ]);
    }

    public function store(StoreClienteRequest $request)
    {
        $cliente = $this->clienteService->create($request->validated());

        return response()->json([
            'message' => 'Cliente creado correctamente',
            'data'    => $cliente,
        ], 201);
    }

    public function show($id)
    {
        $cliente = $this->clienteService->getById((int) $id);

        return response()->json([
            'data' => $cliente,
        ]);
    }

    public function update(UpdateClienteRequest $request, $id)
    {
        $cliente = $this->clienteService->getById((int) $id);
        $cliente = $this->clienteService->update($cliente, $request->validated());

        return response()->json([
            'message' => 'Cliente actualizado correctamente',
            'data'    => $cliente,
        ]);
    }

    public function destroy($id)
    {
        $cliente = $this->clienteService->getById((int) $id);
        $this->clienteService->delete($cliente);

        return response()->json([
            'message' => 'Cliente eliminado correctamente',
        ]);
    }
}

