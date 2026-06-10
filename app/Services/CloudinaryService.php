<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;

class CloudinaryService
{
    private ?Cloudinary $cloudinary = null;

    private function getClient(): Cloudinary
    {
        if (!$this->cloudinary) {
            $this->cloudinary = new Cloudinary(config('services.cloudinary.url', env('CLOUDINARY_URL')));
        }
        return $this->cloudinary;
    }

    public function subirImagen(UploadedFile $imagen, string $carpeta): array
    {
        $result = $this->getClient()->uploadApi()->upload($imagen->getRealPath(), [
            'folder'        => $carpeta,
            'resource_type' => 'image',
        ]);

        return [
            'url'       => $result['secure_url'],
            'public_id' => $result['public_id'],
        ];
    }

    public function eliminarImagen(?string $publicId): void
    {
        if (!$publicId) {
            return;
        }

        $this->getClient()->uploadApi()->destroy($publicId);
    }
}
