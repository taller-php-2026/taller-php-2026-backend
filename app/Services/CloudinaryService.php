<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;

class CloudinaryService
{
    private ?Cloudinary $cloudinary = null;

    private function getClient(): ?Cloudinary
    {
        $url = config('services.cloudinary.url');
        if (!$url) {
            return null;
        }
        if (!$this->cloudinary) {
            $this->cloudinary = new Cloudinary($url);
        }
        return $this->cloudinary;
    }

    public function subirImagen(UploadedFile $imagen, string $carpeta): array
    {
        // Almacenar imagen.
        $client = $this->getClient();
        if (!$client) {
            $uploadPath = public_path('uploads');
            if (! is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $nombre = time() . '_' . uniqid() . '.' . $imagen->getClientOriginalExtension();
            $imagen->move($uploadPath, $nombre);
            $url = asset('uploads/' . $nombre);

            return [
                'url'       => $url,
                'public_id' => 'local_' . $nombre,
            ];
        }

        $result = $client->uploadApi()->upload($imagen->getRealPath(), [
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
        // Eliminar imagen.
        if (!$publicId) {
            return;
        }

        if (str_starts_with($publicId, 'local_')) {
            $filename = str_replace('local_', '', $publicId);
            $path = public_path('uploads/' . $filename);
            if (file_exists($path)) {
                unlink($path);
            }
            return;
        }

        $client = $this->getClient();
        if ($client) {
            $client->uploadApi()->destroy($publicId);
        }
    }
}
