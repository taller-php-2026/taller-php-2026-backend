<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminPaquetesRequest;
use App\Http\Requests\AdminReservasAgrupadasRequest;
use App\Http\Requests\AdminReservasRequest;
use App\Models\Administrador;
use App\Services\AdminMetricasService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct(private AdminMetricasService $adminService) {}

    private function checkAdmin(Request $request): void
    {
        abort_unless(
            Administrador::where('idUsuario', $request->user()->idUsuario)->exists(),
            403,
            'Acceso restringido a administradores.'
        );
    }

    public function metricas(Request $request)
    {
        $this->checkAdmin($request);

        return response()->json([
            'message' => 'Métricas obtenidas correctamente',
            'data'    => $this->adminService->metricas(),
        ]);
    }

    public function reservas(AdminReservasRequest $request)
    {
        $this->checkAdmin($request);

        $paginator = $this->adminService->reservas($request->validated());

        return response()->json([
            'message' => 'Reservas obtenidas correctamente',
            'data'    => $paginator->items(),
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    public function reservasPorProfesional(AdminReservasAgrupadasRequest $request)
    {
        $this->checkAdmin($request);

        return response()->json([
            'message' => 'Reservas por profesional obtenidas correctamente',
            'data'    => $this->adminService->reservasPorProfesional($request->validated()),
        ]);
    }

    public function reservasPorServicio(AdminReservasAgrupadasRequest $request)
    {
        $this->checkAdmin($request);

        return response()->json([
            'message' => 'Reservas por servicio obtenidas correctamente',
            'data'    => $this->adminService->reservasPorServicio($request->validated()),
        ]);
    }

    public function paquetes(AdminPaquetesRequest $request)
    {
        $this->checkAdmin($request);

        $paginator = $this->adminService->paquetes($request->validated());

        return response()->json([
            'message' => 'Paquetes comprados obtenidos correctamente',
            'data'    => $paginator->items(),
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    public function resumenPaquetes(Request $request)
    {
        $this->checkAdmin($request);

        return response()->json([
            'message' => 'Resumen de paquetes obtenido correctamente',
            'data'    => $this->adminService->resumenPaquetes(),
        ]);
    }

    public function paquetesPorServicio(Request $request)
    {
        $this->checkAdmin($request);

        return response()->json([
            'message' => 'Paquetes por servicio obtenidos correctamente',
            'data'    => $this->adminService->paquetesPorServicio(),
        ]);
    }
}
