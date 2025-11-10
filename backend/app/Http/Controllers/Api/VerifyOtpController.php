<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VerifyOtpRequest;
use App\Http\Resources\UserResource;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class VerifyOtpController extends Controller
{
    public function __invoke(VerifyOtpRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $email = $validated['email'];
        $code = $validated['code'];
        $throttleKey = $this->throttleKey($request, $email);

        // Rate limiting específico para verificación
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return response()->json([
                'message' => 'Demasiados intentos fallidos. Intenta nuevamente en '.$seconds.' segundos.',
            ], 429);
        }

        // Buscar OTP válido
        $otp = Otp::where('email', $email)
            ->where('code', $code)
            ->whereNull('used_at')
            ->first();

        if (! $otp || $otp->isExpired()) {
            RateLimiter::hit($throttleKey);

            return response()->json([
                'message' => 'Código inválido o expirado.',
            ], 400);
        }

        // Marcar OTP como usado
        $otp->markAsUsed();

        // Buscar o crear usuario
        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => explode('@', $email)[0]]
        );

        // Crear token de Sanctum
        $token = $user->createToken('auth-token')->plainTextToken;

        // Limpiar rate limit en caso de éxito
        RateLimiter::clear($throttleKey);

        return response()->json([
            'user' => UserResource::make($user),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    private function throttleKey($request, string $email): string
    {
        return Str::transliterate(Str::lower($email).'|verify|'.$request->ip());
    }
}
