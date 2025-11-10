<?php

namespace Tests\Feature;

use App\Models\Otp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint_works(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'timestamp',
                'version',
            ]);
    }

    public function test_send_otp_requires_email(): void
    {
        $response = $this->postJson('/api/auth/send-otp', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_send_otp_requires_valid_email(): void
    {
        $response = $this->postJson('/api/auth/send-otp', [
            'email' => 'invalid-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_send_otp_creates_otp_record(): void
    {
        $email = 'test@example.com';

        $response = $this->postJson('/api/auth/send-otp', [
            'email' => $email,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('otps', [
            'email' => $email,
        ]);
    }

    public function test_verify_otp_requires_email_and_code(): void
    {
        $response = $this->postJson('/api/auth/verify-otp', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'code']);
    }

    public function test_verify_otp_with_valid_code_creates_user_and_token(): void
    {
        $email = 'test@example.com';
        $otp = Otp::createForEmail($email);

        $response = $this->postJson('/api/auth/verify-otp', [
            'email' => $email,
            'code' => $otp->code,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'email',
                ],
                'access_token',
                'token_type',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => $email,
        ]);
    }

    public function test_verify_otp_with_invalid_code_fails(): void
    {
        $email = 'test@example.com';
        Otp::createForEmail($email);

        $response = $this->postJson('/api/auth/verify-otp', [
            'email' => $email,
            'code' => '000000',
        ]);

        $response->assertStatus(400);
    }

    public function test_authenticated_user_can_access_protected_routes(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                ],
            ]);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        $response->assertStatus(204);
    }
}