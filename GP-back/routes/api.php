<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
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

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
    ]);

    return response()->json([
        'message' => 'Usuario registrado correctamente. Ahora puedes iniciar sesión.',
    ], 201);
});


Route::middleware('auth:sanctum')->post('/logout', function (Request $request) {
    $request->user()->tokens()->delete();
    return response()->json(['message' => 'Sesión cerrada con éxito']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/test', function () {
    return response()->json(['message' => 'Conexión exitosa con Laravel 🚀']);
});