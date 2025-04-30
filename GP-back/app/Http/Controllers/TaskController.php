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
            'tags' => 'nullable|string',
            'priority' => 'required|in:baja,media,alta',
            'archived' => 'boolean'
        ]);
        $task = $project->tasks()->create([
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status,
            'due_date' => $request->due_date,
            'tags' => $request->tags,
            'priority' => $request->priority,
            'archived' => $request->archived
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

    public function allUserTasks(Request $request)
    {
        $user = auth()->user();

        $query = Task::whereHas('project', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with('project:id,name');

        // Filtro por estado
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filtro por fecha límite (antes, después, igual)
        if ($request->filled('due_before')) {
            $query->whereDate('due_date', '<=', $request->input('due_before'));
        }
        if ($request->filled('due_after')) {
            $query->whereDate('due_date', '>=', $request->input('due_after'));
        }
        if ($request->filled('due_on')) {
            $query->whereDate('due_date', '=', $request->input('due_on'));
        }

        // Filtro por proyecto (por nombre del proyecto)
        if ($request->filled('project_name')) {
            $query->whereHas('project', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('project_name') . '%');
            });
        }

        // Filtro por búsqueda general (en título o descripción)
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->input('search') . '%')
                ->orWhere('description', 'like', '%' . $request->input('search') . '%');
            });
        }

        // Ordenación (opcional)
        if ($request->filled('sort_by')) {
            $sortBy = $request->input('sort_by');
            $direction = $request->input('sort_direction', 'asc');

            if (in_array($sortBy, ['due_date', 'priority', 'status'])) {
                $query->orderBy($sortBy, $direction);
            }
        } else {
            // Default ordenación
            $query->orderBy('due_date', 'asc');
        }

        $tasks = $query->get();

        return response()->json($tasks);
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
