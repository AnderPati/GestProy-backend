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


Route::middleware('auth:sanctum')->get('/profile', function (Request $request) {
    return response()->json($request->user());
});

Route::middleware('auth:sanctum')->post('/profile/update', function (Request $request) {
    $request->validate([
        'name' => 'required|string|min:3',
        'email' => 'required|email|unique:users,email,' . $request->user()->id,
        'password' => 'nullable|string|min:6',
        'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
    ]);

    $user = $request->user();
    $user->name = $request->name;
    $user->email = $request->email;

    if ($request->filled('password')) {
        $user->password = Hash::make($request->password);
    }

    if ($request->hasFile('profile_image')) {
        // Asegurar que la carpeta de almacenamiento exista
        Storage::makeDirectory('public/profiles');
    
        $image = $request->file('profile_image');
        $imageName = time() . '.' . $image->getClientOriginalExtension();
    
        // ✅ Guardar en `storage/app/public/profiles/`
        $image->move(storage_path('app/public/profiles/'), $imageName);
    
        // ✅ Confirmar en logs la ruta correcta
        \Log::info('Imagen guardada realmente en:', ['ruta' => storage_path("app/public/profiles/{$imageName}")]);
    
        $user->profile_image = $imageName;
        $user->save();
    }
    
    
    

    $user->save();

    return response()->json(['message' => 'Perfil actualizado correctamente', 'user' => $user]);
});
