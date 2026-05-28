<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAgendaRequest;
use App\Http\Requests\UpdateAgendaRequest;
use App\Services\AgendaService;

class AgendaController extends Controller
{
    public function __construct(private AgendaService $agendaService) {}

    public function index()
    {
        $agendas = $this->agendaService->getAll();

        return response()->json([
            'data' => $agendas,
        ]);
    }

    public function store(StoreAgendaRequest $request)
    {
        $agenda = $this->agendaService->create($request->validated());

        return response()->json([
            'message' => 'Agenda creada correctamente',
            'data'    => $agenda,
        ], 201);
    }

    public function show($id)
    {
        $agenda = $this->agendaService->getById((int) $id);

        return response()->json([
            'data' => $agenda,
        ]);
    }

    public function update(UpdateAgendaRequest $request, $id)
    {
        $agenda = $this->agendaService->getById((int) $id);
        $agenda = $this->agendaService->update($agenda, $request->validated());

        return response()->json([
            'message' => 'Agenda actualizada correctamente',
            'data'    => $agenda,
        ]);
    }

    public function destroy($id)
    {
        $agenda = $this->agendaService->getById((int) $id);
        $this->agendaService->delete($agenda);

        return response()->json([
            'message' => 'Agenda eliminada correctamente',
        ]);
    }
}
