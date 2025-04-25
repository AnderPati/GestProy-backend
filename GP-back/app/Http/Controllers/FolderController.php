<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Folder;
use App\Models\Project;


class FolderController extends Controller
{
    public function index($projectId)
    {
        $folders = Folder::where('project_id', $projectId)->get();
        return response()->json($folders);
    }

    public function store(Request $request, $projectId)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $folder = Folder::create([
            'project_id' => $projectId,
            'name' => $request->name
        ]);

        return response()->json(['message' => 'Carpeta creada con éxito', 'folder' => $folder]);
    }

    public function update(Request $request, Project $project, Folder $folder)
    {
        try {
            if ($project->user_id !== auth()->id()) {
                return response()->json(['message' => 'No autorizado'], 403);
            }

            $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $folder->update([
                'name' => $request->name
            ]);

            return response()->json(['message' => 'Carpeta renombrada correctamente']);
        } catch (\Exception $e) {
            \Log::error('Error al renombrar carpeta', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error al renombrar carpeta', 'details' => $e->getMessage()], 500);
        }
    }

    public function destroy(Project $project, Folder $folder)
    {
        if ($project->user_id !== auth()->id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // Si quieres evitar eliminar si hay archivos, puedes validar aquí
        if ($folder->files()->count() > 0) {
            return response()->json(['message' => 'La carpeta contiene archivos. Elimínalos primero.'], 409);
        }

        $folder->delete();

        return response()->json(['message' => 'Carpeta eliminada']);
    }
}
