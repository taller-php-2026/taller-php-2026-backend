<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProfesionalRequest;
use App\Http\Requests\UpdateProfesionalRequest;
use App\Services\ProfesionalService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use App\Models\Profesional;
use illuminate\Support\Facades\Hash;
use Cloudinary\Cloudinary;

class ProfesionalController extends Controller
{
    public function __construct(private ProfesionalService $profesionalService) {}

    public function index()
    {
        $this->adminOnly(request());

        $profesionales = $this->profesionalService->getAll();

        return response()->json([
            'data' => $profesionales,
        ]);
    }

    public function store(StoreProfesionalRequest $request)
    {
        $this->adminOnly($request);

        $profesional = $this->profesionalService->create($request->validated());

        return response()->json([
            'message' => 'Profesional creado correctamente',
            'data'    => $profesional,
        ], 201);
    }

    public function show(int $id)
    {
        $this->ensureOwnProfessionalOrAdmin(request(), (int) $id);

        $profesional = $this->profesionalService->getById((int) $id);

        return response()->json([
            'data' => $profesional,
        ]);
    }

    public function update(UpdateProfesionalRequest $request, int $id)
    {
        $this->ensureOwnProfessionalOrAdmin($request, (int) $id);

        $profesional = $this->profesionalService->getById((int) $id);
        $profesional = $this->profesionalService->update($profesional, $request->validated());

        return response()->json([
            'message' => 'Profesional actualizado correctamente',
            'data'    => $profesional,
        ]);
    }
    
    public function updatePerfil(int $id, Request $request)
{
    $cloudinary = new Cloudinary();

    $profesional = Profesional::find($id);

    if (!$profesional) {
        return response()->json([
            'message' => 'Profesional no encontrado'
        ], 404);
    }

    $usuario = $profesional->usuario;

    if (!$usuario) {
        return response()->json([
            'message' => 'Usuario no encontrado'
        ], 404);
    }

    $usuario->update($request->only([
        'nombre',
        'email',
        'telefono'
    ]));

    if ($request->filled('password')) {
        $usuario->update([
            'password' => Hash::make($request->password)
        ]);
    }

    $profesional->update($request->only([
        'nombreNegocio'
    ]));

    if ($request->hasFile('logo')) {

        $logo = $request->file('logo');

        $uploadedLogo = $cloudinary->uploadApi()->upload(
            $logo->getRealPath(),
            [
                'folder' => 'profesionales/logos'
            ]
        );

        $profesional->logo = $uploadedLogo['secure_url'];
    }

    $profesional->save();

    return response()->json([
        'message' => 'Perfil actualizado correctamente',
        'usuario' => $usuario,
        'profesional' => $profesional
    ]);
}
    public function destroy(int $id)
    {
        $this->adminOnly(request());

        $profesional = $this->profesionalService->getById((int) $id);
        $this->profesionalService->delete($profesional);

        return response()->json([
            'message' => 'Profesional eliminado correctamente',
        ]);
    }

    private function ensureOwnProfessionalOrAdmin(Request $request, int $idProfesional): void
    {
        $user = $request->user();
        $user?->loadMissing(['profesional', 'administrador']);

        if ($user && ($user->administrador || ($user->profesional && (int) $user->idUsuario === $idProfesional))) {
            return;
        }

        throw new HttpResponseException(response()->json([
            'message' => 'No tenés permisos para modificar este profesional.',
        ], 403));
    }

    private function adminOnly(Request $request): void
    {
        $user = $request->user();
        $user?->loadMissing('administrador');

        if ($user && $user->administrador) {
            return;
        }

        throw new HttpResponseException(response()->json([
            'message' => 'Solo administradores pueden realizar esta acción.',
        ], 403));
    }
}
