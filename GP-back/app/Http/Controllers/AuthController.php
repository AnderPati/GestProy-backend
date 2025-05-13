<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Google_Client;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use GuzzleHttp\Client as GuzzleClient;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Las credenciales son incorrectas.'
            ], 422);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    public function loginWithGoogle(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $client = new Google_Client(['client_id' => config('services.google.client_id')]);
        $client->setHttpClient(new GuzzleClient(['verify' => false]));

        $payload = $client->verifyIdToken($request->token);

        if (!$payload) {
            return response()->json(['message' => 'Token de Google inválido'], 401);
        }

        $user = User::firstOrCreate(
            ['email' => $payload['email']],
            [
                'name' => $payload['name'],
                'password' => bcrypt(Str::random(32)),
                'auth_provider' => 'google'
            ]
        );

        if (is_null($user->auth_provider)) {
            $user->update(['auth_provider' => 'google']);
        }

        // ✅ Generar token para usar como Bearer
        $token = $user->createToken('google_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => $user,
        ]);
    }


    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Usuario registrado correctamente.',
            'user' => new UserResource($user)
        ], 201);
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();
        return response()->json(['success' => true, 'message' => 'Sesión cerrada con éxito']);
    }
}
