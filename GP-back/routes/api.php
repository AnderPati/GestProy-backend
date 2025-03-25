<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Http\Controllers\ProjectController;

//! Autenticación y Registro
Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['Las credenciales son incorrectas.'],
        ]);
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'token' => $token,
        'user' => $user
    ]);
});

Route::post('/register', function (Request $request) {
    $request->validate([
        'name' => 'required|string|max:255|min:3',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:6',
    ]);

    User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
    ]);

    return response()->json([
        'message' => 'Usuario registrado correctamente. Ahora puedes iniciar sesión.',
    ], 201);
});

//! Cierre de sesión
Route::middleware('auth:sanctum')->post('/logout', function (Request $request) {
    $request->user()->tokens()->delete();
    return response()->json(['message' => 'Sesión cerrada con éxito']);
});

//! Obtener información del usuario autenticado
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json($request->user());
});

//! Perfil de usuario
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', function (Request $request) {
        return response()->json($request->user());
    });

    Route::post('/profile/update', function (Request $request) {
        $request->validate([
            'name' => 'required|string|min:3',
            'email' => 'required|email|unique:users,email,' . $request->user()->id,
            'password' => 'nullable|string|min:6',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $user = $request->user();
        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('current_password') && !Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'La contraseña actual es incorrecta.'], 400);
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($request->hasFile('profile_image')) {
            Storage::makeDirectory('public/profiles');
            $image = $request->file('profile_image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(storage_path('app/public/profiles/'), $imageName);
            \Log::info('Imagen guardada realmente en:', ['ruta' => storage_path("app/public/profiles/{$imageName}")]);
            $user->profile_image = $imageName;
        }

        $user->save();
        return response()->json(['message' => 'Perfil actualizado correctamente', 'user' => $user]);
    });

    Route::delete('/profile/delete-image', function (Request $request) {
        $user = $request->user();
        if ($user->profile_image) {
            Storage::delete('public/profiles/' . $user->profile_image);
            $user->profile_image = null;
            $user->save();
            return response()->json(['message' => 'Imagen eliminada correctamente']);
        }
        return response()->json(['message' => 'No hay imagen para eliminar'], 400);
    });
});

//! Gestión de Proyectos
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('projects', ProjectController::class);
});
