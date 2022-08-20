<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\Concerns\InteractsWithTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostCategoryTest extends TestCase
{
    use RefreshDatabase, InteractsWithTime;

    private int $tokenTtl;

    private int $refreshTokenTtl;

    private bool $seed = true;

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

        $response = $this->get('/api/post-categories', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $this->assertTrue($response->collect()->count() > 0);
    }
}
