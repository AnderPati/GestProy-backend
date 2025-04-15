<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Project;

class TaskController extends Controller
{
    public function index($projectId)
    {
        return Task::where('project_id', $projectId)->orderBy('status')->orderBy('position')->get();
    }

    public function store(Request $request, $projectId)
    {
        $project = Project::find($projectId);

        if (!$project || $project->user_id !== auth()->id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pendiente,en progreso,completado',
            'due_date' => 'nullable|date',
            'tags' => 'nullable|string'
        ]);
        $task = $project->tasks()->create([
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status,
            'due_date' => $request->due_date,
            'tags' => $request->tags,
        ]);

        return response()->json(['message' => 'Tarea creada con éxito', 'task' => $task]);
    }

    public function show($id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json(['message' => 'Tarea no encontrada'], 404);
        }

        return response()->json($task);
    }

    public function update(Request $request, $id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json(['message' => 'Tarea no encontrada'], 404);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pendiente,en progreso,completado',
            'due_date' => 'nullable|date',
            'tags' => 'nullable|string',
        ]);

        $task->update($request->all());

        return response()->json(['message' => 'Tarea actualizada', 'task' => $task]);
    }
    
    public function destroy($id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json(['message' => 'Tarea no encontrada'], 404);
        }

        $task->delete();

        return response()->json(['message' => 'Tarea eliminada']);
    }
}
