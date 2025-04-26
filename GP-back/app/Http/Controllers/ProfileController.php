<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Services\ProfileImageService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    protected $imageService;

    public function __construct(ProfileImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function show(Request $request)
    {
        return new UserResource($request->user());
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('current_password') && !Hash::check($request->current_password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'La contraseña actual es incorrecta.'], 400);
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($request->hasFile('profile_image')) {
            $imageName = $this->imageService->store($request->file('profile_image'), $user->profile_image);
            $user->profile_image = $imageName;
        }

        $user->save();
        return response()->json([
            'success' => true,
            'message' => 'Perfil actualizado correctamente.',
            'user' => new UserResource($user),
        ]);
    }

    public function deleteImage(Request $request)
    {
        $user = $request->user();
        if ($user->profile_image) {
            $this->imageService->delete($user->profile_image);
            $user->profile_image = null;
            $user->save();
            return response()->json(['success' => true, 'message' => 'Imagen eliminada correctamente']);
        }
        return response()->json(['success' => false, 'message' => 'No hay imagen para eliminar'], 400);
    }

    public function storageUsage()
    {
        $user = auth()->user();

        // Obtener todos los IDs de proyectos del usuario
        $projectIds = $user->projects()->pluck('id');

        // Sumar el tamaño total de todos los archivos de esos proyectos
        $usedBytes = \App\Models\ProjectFile::whereIn('project_id', $projectIds)->sum('size');

        // Devolver información
        return response()->json([
            'storage_limit' => $user->storage_limit, // en bytes
            'used' => $usedBytes, // en bytes
            'free' => max(0, $user->storage_limit - $usedBytes), // para asegurarnos que no dé negativo
        ]);
    }
}

