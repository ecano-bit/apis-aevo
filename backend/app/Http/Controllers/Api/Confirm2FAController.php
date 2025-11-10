<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class Confirm2FAController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6', 'regex:/^[0-9]{6}$/'],
        ]);

        $user = $request->user();
        $throttleKey = $this->throttleKey($request);

        // Rate limiting para confirmación 2FA
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return response()->json([
                'message' => 'Demasiados intentos. Intenta nuevamente en '.$seconds.' segundos.',
            ], 429);
        }

        if (! $user->two_factor_secret) {
            return response()->json([
                'message' => '2FA no está configurado para este usuario.',
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

        // Activar 2FA y generar códigos de recuperación
        $recoveryCodes = collect(range(1, 8))->map(fn () => Str::random(10).'-'.Str::random(10));
        
        $user->forceFill([
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => encrypt($recoveryCodes->toJson()),
        ])->save();

        RateLimiter::clear($throttleKey);

        return response()->json([
            'message' => '2FA activado exitosamente.',
            'recovery_codes' => $recoveryCodes,
        ]);
    }

    private function throttleKey(Request $request): string
    {
        return Str::transliterate('2fa-confirm|'.$request->user()->id.'|'.$request->ip());
    }
}
