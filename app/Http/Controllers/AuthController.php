<?php

namespace App\Http\Controllers;

use App\Models\User;
use Closure;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController
{
    public function register(Request $request): JsonResponse
    {
        $credentials = $this->credentials($request, registering: true);

        try {
            $user = User::create([
                'name' => $credentials['name'],
                'email' => strtolower($credentials['email']),
                'password' => Hash::make($credentials['password']),
            ]);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['email' => ['Email sudah terdaftar.']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil.',
            'data' => $user,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $this->credentials($request);
        $user = User::where('email', strtolower($credentials['email']))->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau kata sandi salah.',
            ], 401);
        }

        $expiresAt = now()->addDay();
        $token = $user->createToken('api', ['*'], $expiresAt);

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data' => [
                'token' => $token->plainTextToken,
                'token_type' => 'Bearer',
                'expires_at' => $expiresAt->toISOString(),
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Data pengguna berhasil diambil.',
            'data' => $request->user(),
        ]);
    }

    private function credentials(Request $request, bool $registering = false): array
    {
        $passwordRules = ['bail', 'required', 'string', function (string $attribute, string $value, Closure $fail): void {
            if (strlen($value) > 72 || str_contains($value, chr(0))) {
                $fail('Kata sandi maksimal 72 byte dan tidak boleh memuat karakter null.');
            }
        }];

        $rules = [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => $passwordRules,
        ];

        if ($registering) {
            $rules['name'] = ['required', 'string', 'max:255'];
            $rules['password'][] = 'min:8';
        }

        return $request->validate($rules, [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'email.email' => 'Format email tidak valid.',
            'max.string' => ':attribute maksimal :max karakter.',
            'password.min' => 'Kata sandi minimal :min karakter.',
        ], ['name' => 'Nama', 'email' => 'Email', 'password' => 'Kata sandi']);
    }
}
