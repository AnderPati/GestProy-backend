<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class ProfileImageService
{
    public function store(UploadedFile $image, ?string $oldImageName = null): string
    {
        $directory = storage_path('app/public/profiles');

        // Borrar la anterior
        if ($oldImageName) {
            $oldPath = "$directory/$oldImageName";
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        // Asegurar que el directorio exista
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        // Guardar la imagen con PHP directamente
        $imageName = uniqid('profile_', true) . '.' . $image->getClientOriginalExtension();
        \Log::info('📦 Info previa al guardado', [
            'target' => storage_path('app/public/profiles'),
            'real_target' => realpath(storage_path('app/public/profiles')) ?: 'NO EXISTE',
            'archivo_temp' => $image->getRealPath(),
        ]);
        $image->move($directory, $imageName);
        \Log::info('✅ Imagen movida a storage/app/public/profiles');

        return $imageName;
    }

    public function delete(string $imageName): void
    {
        $path = "profiles/$imageName";

    if (Storage::disk('public')->exists($path)) {
        Storage::disk('public')->delete($path);
    }
    }
}
