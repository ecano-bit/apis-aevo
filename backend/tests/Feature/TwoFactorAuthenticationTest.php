<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_enable_2fa(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/2fa/enable');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'secret',
                'qr_code_url',
                'message',
            ]);

        $this->assertNotNull($user->fresh()->two_factor_secret);
    }

    public function test_user_cannot_enable_2fa_twice(): void
    {
        $user = User::factory()->create([
            'two_factor_secret' => encrypt('test-secret'),
        ]);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/2fa/enable');

        $response->assertStatus(400);
    }

    public function test_user_can_confirm_2fa_with_valid_code(): void
    {
        $user = User::factory()->create();
        $secret = app(Google2FA::class)->generateSecretKey();
        $user->forceFill(['two_factor_secret' => encrypt($secret)])->save();

        $token = $user->createToken('test-token')->plainTextToken;
        $validCode = app(Google2FA::class)->getCurrentOtp($secret);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/2fa/confirm', [
            'code' => $validCode,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'recovery_codes',
            ]);

        $this->assertNotNull($user->fresh()->two_factor_confirmed_at);
    }

    public function test_user_cannot_confirm_2fa_with_invalid_code(): void
    {
        $user = User::factory()->create();
        $secret = app(Google2FA::class)->generateSecretKey();
        $user->forceFill(['two_factor_secret' => encrypt($secret)])->save();

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/2fa/confirm', [
            'code' => '000000',
        ]);

        $response->assertStatus(400);
    }

    public function test_user_can_disable_2fa(): void
    {
        $user = User::factory()->create([
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_confirmed_at' => now(),
        ]);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/2fa/disable');

        $response->assertStatus(204);

        $user = $user->fresh();
        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_confirmed_at);
    }

    public function test_user_can_login_with_2fa_code(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $secret = app(Google2FA::class)->generateSecretKey();
        $user->forceFill([
            'two_factor_secret' => encrypt($secret),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $validCode = app(Google2FA::class)->getCurrentOtp($secret);

        $response = $this->postJson('/api/auth/verify-2fa', [
            'email' => 'test@example.com',
            'code' => $validCode,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user',
                'access_token',
                'token_type',
            ]);
    }

    public function test_user_cannot_login_with_invalid_2fa_code(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $secret = app(Google2FA::class)->generateSecretKey();
        $user->forceFill([
            'two_factor_secret' => encrypt($secret),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $response = $this->postJson('/api/auth/verify-2fa', [
            'email' => 'test@example.com',
            'code' => '000000',
        ]);

        $response->assertStatus(400);
    }
}