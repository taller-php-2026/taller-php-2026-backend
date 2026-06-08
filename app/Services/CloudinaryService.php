<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;

class CloudinaryService
{
    private Cloudinary $cloudinary;

    public function __construct()
    {
        $this->cloudinary = new Cloudinary(config('services.cloudinary.url', env('CLOUDINARY_URL')));
    }

    /**
     * Sube una imagen a Cloudinary en la carpeta indicada.
     *
     * @return array{ url: string, public_id: string }
     */
    public function subirImagen(UploadedFile $imagen, string $carpeta): array
    {
        $result = $this->cloudinary->uploadApi()->upload($imagen->getRealPath(), [
            'folder'        => $carpeta,
            'resource_type' => 'image',
        ]);

        return [
            'url'       => $result['secure_url'],
            'public_id' => $result['public_id'],
        ];
    }

    /**
     * Elimina una imagen de Cloudinary por su public_id.
     * No lanza excepción si el public_id es null o vacío.
     */
    public function eliminarImagen(?string $publicId): void
    {
        if (! $publicId) {
            return;
        }

        $this->cloudinary->uploadApi()->destroy($publicId);
    }
}
