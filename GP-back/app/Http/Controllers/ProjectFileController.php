<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProjectFile;
use App\Models\Project;
use Illuminate\Support\Facades\Storage;

class ProjectFileController extends Controller
{
    public function index($projectId)
    {
        $project = Project::findOrFail($projectId);

        if ($project->user_id !== auth()->id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return $project->files()->get();
    }

    public function store(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);

        if ($project->user_id !== auth()->id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        \Log::info('Archivo recibido:', ['keys' => array_keys($request->all())]);

        $request->validate([
            'file' => 'required|file|max:51200' // 50MB
        ]);

        try {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $shortenedOriginal = substr($originalName, -150); // Últimos 150 caracteres del nombre original
            $storedName = uniqid() . '_' . $shortenedOriginal;

            //Variable para test (logs o validaciones)
            $path = $file->storeAs("public/projects/{$projectId}/files", $storedName);

                $projectFile = ProjectFile::create([
                    'project_id' => $projectId,
                    'original_name' => $originalName,
                    'stored_name' => $storedName,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize()
                ]);

            return response()->json(['message' => 'Archivo subido correctamente', 'file' => $projectFile]);

        } catch (\Exception $e) {
            \Log::error('Error al subir archivo', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Hubo un problema al subir el archivo.',
                'details' => $e->getMessage()
            ], 500);
        }

    }

    public function download($id)
    {
        $file = ProjectFile::findOrFail($id);
        $project = $file->project;

        if ($project->user_id !== auth()->id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $path = "public/projects/{$project->id}/files/{$file->stored_name}";

        if (!Storage::exists($path)) {
            return response()->json(['message' => 'Archivo no encontrado'], 404);
        }

        return Storage::download($path, $file->original_name);
    }

    public function destroy($id)
    {
        $file = ProjectFile::findOrFail($id);
        $project = $file->project;

        if ($project->user_id !== auth()->id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        Storage::delete("public/projects/{$project->id}/files/{$file->stored_name}");
        $file->delete();

        return response()->json(['message' => 'Archivo eliminado']);
    }
}
