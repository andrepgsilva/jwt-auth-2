<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\Concerns\InteractsWithTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAuthTest extends TestCase
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

    /**
     * Test if the user can get tokens on sign up
     *
     * @return void
     */
    public function test_if_user_can_get_tokens_when_it_register(): void
    {
        $signUp = $this->signup();
        $response = $signUp['response'];

        $response = $response->collect();

        $tokens = isset($response['authorization']['token']) && isset($response['authorization']['refresh_token']);
        $this->assertTrue($tokens);
    }

    /**
     * Test if the user can get a token on login
     *
     * @return void
     */
    public function test_if_user_can_get_tokens_when_it_tries_to_login(): void
    {
        $signUp = $this->signup();
        $userInformation = $signUp['userInformation'];
        unset($userInformation['name']);

        $response = $this->postJson('/api/auth/login', $userInformation);

        $tokens = isset($response['authorization']['token']) && isset($response['authorization']['refresh_token']);
        $this->assertTrue($tokens);
    }

    /**
     * Test if the user can refresh a token
     *
     * @return void
     */
    public function test_if_user_can_refresh_a_token(): void
    {
        $signUp = $this->signup();
        $userInformation = $signUp['userInformation'];
        unset($userInformation['name']);

        $response = $this->postJson('/api/auth/login', $userInformation);
        $token = $response['authorization']['token'];
        $refreshToken = $response['authorization']['refresh_token'];

        $this->travel($this->refreshTokenTtl - 1)->minutes(function () use ($refreshToken) {
            $response = $this->post('/api/auth/refresh', [], [
                'Authorization' => 'Bearer '.$refreshToken,
            ]);

            $tokens = isset($response['authorization']['token']) && isset($response['authorization']['refresh_token']);
            $this->assertTrue($tokens);
        });
    }

    /**
     * Test if the user can refresh a token
     *
     * @return void
     */
    public function test_if_user_cannot_refresh_a_token_when_it_expired(): void
    {
        $signUp = $this->signup();
        $userInformation = $signUp['userInformation'];
        unset($userInformation['name']);

        $response = $this->postJson('/api/auth/login', $userInformation);
        $refreshToken = $response['authorization']['refresh_token'];

        $this->travel($this->refreshTokenTtl + 1)->minutes(function () use ($refreshToken) {
            $response = $this->post('/api/auth/refresh', [], [
                'Authorization' => 'Bearer '.$refreshToken,
            ]);

            $this->assertTrue(in_array($response->status(), [400, 401]));
        });
    }

    /**
     * Test if the user can refresh a token
     *
     * @return void
     */
    public function test_if_user_can_logout(): void
    {
        $signUp = $this->signup();
        $response = $signUp['response'];
        $response = $response->collect();

        $token = $response['authorization']['token'];

        $response = $this->postJson('/api/auth/logout', [], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertOk();
    }
}
