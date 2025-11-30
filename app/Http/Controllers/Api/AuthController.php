<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\CarbonImmutable;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'] ?? 'etudiant',
            'filiere' => $data['role'] === 'etudiant' ? $data['filiere'] ?? null : null,
        ]);

        $accessToken = $user->createToken('api')->plainTextToken;

        $refreshTokenString = Str::random(64);
        RefreshToken::create([
            'user_id' => $user->id,
            'token' => hash('sha256', $refreshTokenString),
            'expires_at' => CarbonImmutable::now()->addDays(30),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'message' => 'User registered successfully.',
            'data' => [
                'access_token' => $accessToken,
                'refresh_token' => $refreshTokenString,
                'user' => $user,
            ],
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        /** @var User|null $user */
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials.',
                'errors' => [
                    'email' => ['These credentials do not match our records.'],
                ],
            ], 422);
        }

        $accessToken = $user->createToken('api')->plainTextToken;

        $refreshTokenString = Str::random(64);
        $refreshToken = RefreshToken::create([
            'user_id' => $user->id,
            'token' => hash('sha256', $refreshTokenString),
            'expires_at' => CarbonImmutable::now()->addDays(30),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'message' => 'Login successful.',
            'data' => [
                'access_token' => $accessToken,
                'refresh_token' => $refreshTokenString,
                'user' => $user,
            ],
        ]);
    }

    public function refresh(Request $request): JsonResponse
    {
        $request->validate([
            'refresh_token' => ['required', 'string'],
        ]);

        $hashed = hash('sha256', $request->input('refresh_token'));

        /** @var RefreshToken|null $stored */
        $stored = RefreshToken::where('token', $hashed)
            ->where('expires_at', '>', now())
            ->first();

        if (!$stored) {
            return response()->json([
                'message' => 'Invalid refresh token.',
                'errors' => [
                    'refresh_token' => ['This refresh token is invalid or expired.'],
                ],
            ], 401);
        }

        $user = $stored->user;

        $accessToken = $user->createToken('api')->plainTextToken;

        // Optionally rotate refresh token
        $newRefreshPlain = Str::random(64);
        $stored->update([
            'token' => hash('sha256', $newRefreshPlain),
            'expires_at' => now()->addDays(30),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'message' => 'Token refreshed.',
            'data' => [
                'access_token' => $accessToken,
                'refresh_token' => $newRefreshPlain,
                'user' => $user,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($request->user()?->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }

        RefreshToken::where('user_id', $user->id)
            ->where('ip_address', $request->ip())
            ->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
            'data' => null,
        ]);
    }
}



