<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Enable2FAController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->two_factor_secret) {
            return response()->json([
                'message' => '2FA ya está habilitado para este usuario.',
            ], 400);
        }

        // Generar secret para 2FA usando Fortify
        $user->forceFill([
            'two_factor_secret' => encrypt(app(\PragmaRX\Google2FA\Google2FA::class)->generateSecretKey()),
        ])->save();

        $qrCodeUrl = app(\PragmaRX\Google2FA\Google2FA::class)->getQRCodeUrl(
            config('app.name'),
            $user->email,
            decrypt($user->two_factor_secret)
        );

        return response()->json([
            'secret' => decrypt($user->two_factor_secret),
            'qr_code_url' => $qrCodeUrl,
            'message' => '2FA configurado. Escanea el código QR con Google Authenticator.',
        ]);
    }
}
