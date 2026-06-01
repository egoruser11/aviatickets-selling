<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $request->merge([
            'name' => trim((string) $request->input('name', '')),
            'email' => str($request->input('email', ''))->trim()->lower()->toString(),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => User::ROLE_USER,
            'balance' => 0,
        ]);

        return response()->json([
            'token' => $user->createToken('avia-api')->plainTextToken,
            'user' => $user,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->merge([
            'email' => str($request->input('email', ''))->trim()->lower()->toString(),
        ]);

        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Неверный email или пароль.',
            ]);
        }

        return response()->json([
            'token' => $user->createToken('avia-api')->plainTextToken,
            'user' => $user,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Токен удален.']);
    }
}
