<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class Verify2FAController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'string', 'size:6', 'regex:/^[0-9]{6}$/'],
        ]);

        $throttleKey = $this->throttleKey($request);

        // Rate limiting
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return response()->json([
                'message' => 'Demasiados intentos. Intenta nuevamente en '.$seconds.' segundos.',
            ], 429);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user || ! $user->two_factor_secret) {
            RateLimiter::hit($throttleKey);

            return response()->json([
                'message' => 'Usuario no encontrado o 2FA no configurado.',
            ], 400);
        }

        $valid = app(\PragmaRX\Google2FA\Google2FA::class)->verifyKey(
            decrypt($user->two_factor_secret),
            $request->code
        );

        if (! $valid) {
            RateLimiter::hit($throttleKey);

            return response()->json([
                'message' => 'Código 2FA inválido.',
            ], 400);
        }

        // Crear token de Sanctum
        $token = $user->createToken('auth-token')->plainTextToken;

        RateLimiter::clear($throttleKey);

        return response()->json([
            'user' => UserResource::make($user),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    private function throttleKey(Request $request): string
    {
        return Str::transliterate('2fa-verify|'.Str::lower($request->email).'|'.$request->ip());
    }
}
