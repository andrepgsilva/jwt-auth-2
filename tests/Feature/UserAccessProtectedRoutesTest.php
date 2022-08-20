<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\Concerns\InteractsWithTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAccessProtectedRoutesTest extends TestCase
{
    use RefreshDatabase, InteractsWithTime;

    private int $tokenTtl;

    private int $refreshTokenTtl;

    public function setUp(): void
    {
        parent::setUp();

        $this->tokenTtl = env('TOKEN_TTL', 1);
        $this->refreshTokenTtl = env('REFRESH_TOKEN_TTL', 2);
    }

    private function signup(): array
    {
        $userInfo = [
            'name' => 'Penitent One',
            'email' => 'penitentone@example.com',
            'password' => 'USu23!3A1@1se',
        ];

        $response = $this->postJson('/api/auth/register', $userInfo);

        return [
            'response' => $response,
            'userInformation' => $userInfo,
        ];
    }

    public function test_if_user_can_access_a_protected_route(): void
    {
        $signUp = $this->signup();
        $response = $signUp['response'];
        $response = $response->collect();

        $token = $response['authorization']['token'];

        $response = $this->get('/api/test', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertOk();
    }

    public function test_if_user_cannot_access_a_protected_route_with_an_expired_token(): void
    {
        $signUp = $this->signup();
        $response = $signUp['response'];
        $response = $response->collect();

        $token = $response['authorization']['token'];

        $this->travel($this->tokenTtl + 1)->minutes(function () use ($token) {
            $response = $this->get('/api/test', [
                'Authorization' => 'Bearer '.$token,
            ]);

            $this->assertTrue($response->status() == 401);
        });
    }

    public function test_if_user_cannot_access_a_protected_route_with_a_malformed_token(): void
    {
        $signUp = $this->signup();
        $response = $signUp['response'];
        $response = $response->collect();

        $token = $response['authorization']['token'];

        $response = $this->get('/api/test', [
            'Authorization' => 'Bearer '.$token.'arWDDqq12355g<<<yr',
        ]);

        $this->assertTrue($response->status() == 400);
    }

    public function test_if_user_cannot_access_a_protected_route_with_a_blacklisted_token(): void
    {
        $signUp = $this->signup();
        $response = $signUp['response'];
        $response = $response->collect();

        $token = $response['authorization']['token'];

        $response = $this->post('/api/auth/logout', [], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response = $this->get('/api/test', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $this->assertTrue($response->status() == 401);
    }
}
