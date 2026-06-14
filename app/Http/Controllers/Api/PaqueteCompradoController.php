<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PagarPaqueteCompradoRequest;
use App\Http\Requests\StorePaqueteCompradoRequest;
use App\Models\Administrador;
use App\Services\PaqueteCompradoService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PaqueteCompradoController extends Controller
{
    public function __construct(private PaqueteCompradoService $paqueteCompradoService) {}

    public function comprar(StorePaqueteCompradoRequest $request, $id)
    {
        $user = $request->user();
        $user?->loadMissing('cliente');

        if (! $user || ! $user->cliente) {
            throw new HttpException(403, 'Solo los clientes pueden comprar paquetes.');
        }

        $paqueteComprado = $this->paqueteCompradoService->comprar((int) $id, (int) $user->idUsuario);

        return response()->json([
            'message' => 'Compra de paquete creada correctamente',
            'data'    => [
                'paqueteComprado' => $paqueteComprado,
            ],
        ], 201);
    }

    public function pagar(PagarPaqueteCompradoRequest $request, $id)
    {
        $paqueteComprado = $this->paqueteCompradoService->getById((int) $id);
        $this->paqueteCompradoService->assertClienteOwner($paqueteComprado, $request->user());

        $result = $this->paqueteCompradoService->pagar((int) $id, $request->validated());

        return response()->json([
            'message' => 'Paquete pagado correctamente',
            'data'    => $result,
        ]);
    }

    public function misPaquetes(Request $request)
    {
        $user = $request->user();
        $user?->loadMissing('cliente');

        if (! $user || ! $user->cliente) {
            throw new HttpException(403, 'Solo los clientes pueden consultar sus paquetes.');
        }

        $paquetes = $this->paqueteCompradoService->getByCliente((int) $user->idUsuario);

        return response()->json([
            'message' => 'Paquetes del cliente obtenidos correctamente',
            'data'    => $paquetes,
        ]);
    }

    public function porCliente(Request $request, $id)
    {
        $user = $request->user();
        $isOwner = (int) $user->idUsuario === (int) $id && $user->cliente;
        $isAdmin = Administrador::where('idUsuario', $user->idUsuario)->exists();

        if (! $isOwner && ! $isAdmin) {
            throw new HttpException(403, 'No tenes permisos para consultar paquetes de otro cliente.');
        }

        $paquetes = $this->paqueteCompradoService->getByCliente((int) $id);

        return response()->json([
            'message' => 'Paquetes del cliente obtenidos correctamente',
            'data' => $paquetes,
        ]);
    }
}
