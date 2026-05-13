<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $request->authenticate();

<<<<<<< adam
        $user = $request->authenticate();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user->only(['id', 'name', 'email', 'role']),
=======
        $user = $request->user();

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $request->user()->only(['id', 'name', 'email']),
>>>>>>> feat/Fundacao
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }
}
