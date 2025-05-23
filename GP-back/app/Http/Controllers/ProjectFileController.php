<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProjectFile;
use App\Models\Project;
use App\Models\Folder;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ProjectFileController extends Controller
{
    public function index($projectId)
    {
        $project = Project::findOrFail($projectId);

        if ($project->user_id !== auth()->id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return ProjectFile::with('folder')->where('project_id', $projectId)->get();
    }

    public function store(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);

        if ($project->user_id !== auth()->id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        \Log::info('Archivo recibido:', ['keys' => array_keys($request->all())]);

        $request->validate([
            'file' => 'required|file|max:51200', // 50MB
            'folder_id' => 'nullable|exists:folders,id'
        ]);

        try {
            $user = auth()->user();

            // Calcular el espacio usado actual por este usuario
            $usedStorage = ProjectFile::whereHas('project', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->sum('size');

            $incomingFileSize = $request->file('file')->getSize();
            $newTotal = $usedStorage + $incomingFileSize;
    
            if ($newTotal > $user->storage_limit) {
                return response()->json(['message' => 'Has excedido tu límite de almacenamiento.'], 400);
            }

            $file = $request->file('file');
            $folderId = $request->input('folder_id');

            $folderPath = '';
            if ($folderId) {
                $folder = Folder::findOrFail($folderId);
                if ($folder->project_id !== $project->id) {
                    return response()->json(['message' => 'Carpeta no válida para este proyecto.'], 400);
                }
                $folderPath = "/{$folder->name}";
            }

            $originalName = $file->getClientOriginalName();
            $shortenedOriginal = substr($originalName, -150);
            $storedName = uniqid() . '_' . $shortenedOriginal;

            $storagePath = "projects/{$projectId}/files" . $folderPath;
            $file->storeAs($storagePath, $storedName);

            $projectFile = ProjectFile::create([
                'project_id' => $projectId,
                'folder_id' => $folderId,
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

        $folderPath = $file->folder ? "/{$file->folder->name}" : '';
        $path = "projects/{$project->id}/files{$folderPath}/{$file->stored_name}";

        if (!Storage::exists($path)) {
            return response()->json(['message' => 'Archivo no encontrado'], 404);
        }

        return Storage::download($path, $file->original_name);
    }

    public function downloadFolder(Folder $folder)
    {
        $project = $folder->project;

        if ($project->user_id !== auth()->id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $folderName = $folder->name;

        // Normalizamos espacios o caracteres especiales para evitar fallos
        $folderNameSanitized = str_replace([' ', '%20'], '_', $folderName);

        $folderPath = "projects/{$project->id}/files/{$folderNameSanitized}";
        $storagePath = storage_path("app/private/{$folderPath}");

        if (!file_exists($storagePath)) {
            \Log::error('No se encontró carpeta para descargar', ['ruta' => $storagePath]);
            return response()->json(['message' => 'Carpeta no encontrada.'], 404);
        }

        // Asegurarse que existe la carpeta temporal
        $tempDir = storage_path("app/temp");
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zipFileName = $folderNameSanitized . '.zip';
        $zipPath = $tempDir . '/' . $zipFileName;

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($storagePath),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $name => $file) {
                if (!$file->isDir()) {
                    $relativePath = substr($file->getRealPath(), strlen($storagePath) + 1);
                    $zip->addFile($file->getRealPath(), $relativePath);
                }
            }

            $zip->close();
            return response()->download($zipPath)->deleteFileAfterSend(true);
        } else {
            return response()->json(['message' => 'No se pudo crear el zip'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $file = ProjectFile::findOrFail($id);
        $project = $file->project;

        if ($project->user_id !== auth()->id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $request->validate([
            'folder_id' => 'nullable|exists:folders,id' // ermite folder_id null o válido
        ]);

        $oldFolderPath = $file->folder_id ? "projects/{$project->id}/files/{$file->folder->name}" : "projects/{$project->id}/files";
        $newFolderPath = $request->folder_id ? "projects/{$project->id}/files/" . Folder::find($request->folder_id)->name : "projects/{$project->id}/files";

        $oldPath = "{$oldFolderPath}/{$file->stored_name}";
        $newPath = "{$newFolderPath}/{$file->stored_name}";

        if ($oldPath !== $newPath && \Storage::exists($oldPath)) {
            \Storage::move($oldPath, $newPath);
        }

        $file->folder_id = $request->folder_id; // Puede ser null si el usuario así lo quiere
        $file->save();

        return response()->json(['message' => 'Archivo actualizado', 'file' => $file]);
    }

    public function destroy($id)
    {
        $file = ProjectFile::findOrFail($id);
        $project = $file->project;

        if ($project->user_id !== auth()->id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $folderPath = $file->folder ? "/{$file->folder->name}" : '';
        Storage::delete("projects/{$project->id}/files{$folderPath}/{$file->stored_name}");
        $file->delete();

        return response()->json(['message' => 'Archivo eliminado']);
    }
}
