<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendOtpRequest;
use App\Models\Otp;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class SendOtpController extends Controller
{
    public function __invoke(SendOtpRequest $request): JsonResponse
    {
        $email = $request->validated()['email'];
        $throttleKey = $this->throttleKey($request, $email);

        // Rate limiting específico
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return response()->json([
                'message' => 'Demasiados intentos. Intenta nuevamente en '.$seconds.' segundos.',
            ], 429);
        }

        RateLimiter::hit($throttleKey, 300); // 5 minutos

        // Crear OTP
        $otp = Otp::createForEmail($email);

        // Enviar email (configurado con driver)
        try {
            // Mail::to($email)->send(new OtpMail($otp->code));

            // Log para desarrollo
            Log::info("Código OTP para {$email}: {$otp->code}");

            // Siempre devolver éxito (no revelar si email existe)
            return response()->json([
                'message' => 'Si el email existe, recibirás un código de verificación.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error enviando OTP: '.$e->getMessage());

            return response()->json([
                'message' => 'Si el email existe, recibirás un código de verificación.',
            ]);
        }
    }

    private function throttleKey($request, string $email): string
    {
        return Str::transliterate(Str::lower($email).'|'.$request->ip());
    }
}
