<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;

class ProjectController extends Controller {
    // Obtener todos los proyectos
    public function index() {
        return response()->json(Project::where('user_id', auth()->id())->get());
    }

    // Crear un nuevo proyecto
    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|min:3',
            'description' => 'nullable|string',
            'status' => 'required|in:pendiente,en progreso,completado',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date'
        ]);

        $project = Project::create([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'user_id' => auth()->id()
        ]);

        return response()->json(['message' => 'Proyecto creado con éxito', 'project' => $project], 201);
    }

    // Obtener un solo proyecto
    public function show($id) {
        $project = Project::find($id);
        if (!$project) {
            return response()->json(['message' => 'Proyecto no encontrado'], 404);
        }
        return response()->json($project);
    }

    // Actualizar un proyecto
    public function update(Request $request, $id) {
        $project = Project::find($id);
        if (!$project) {
            return response()->json(['message' => 'Proyecto no encontrado'], 404);
        }

        $project->update($request->all());

        return response()->json(['message' => 'Proyecto actualizado con éxito', 'project' => $project]);
    }

    // Eliminar un proyecto
    public function destroy($id) {
        $project = Project::find($id);
        if (!$project) {
            return response()->json(['message' => 'Proyecto no encontrado'], 404);
        }

        $project->delete();

        return response()->json(['message' => 'Proyecto eliminado con éxito']);
    }
}
