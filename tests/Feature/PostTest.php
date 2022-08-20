<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Post;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\Concerns\InteractsWithTime;

class PostTest extends TestCase
{
    use RefreshDatabase, InteractsWithTime;

    private bool $seed = true;

    private function signup(): array
    {
        $userInfo = [
            'name' => 'Penitent One',
            'email' => 'example@example.com',
            'password' => 'USu23!3A1@1se',
        ];

        $response = $this->postJson('/api/auth/register', $userInfo);

        return [
            'response' => $response,
            'userInformation' => $userInfo,
        ];
    }

    public function test_if_user_can_create_a_post(): void
    {
        $signUp = $this->signup();
        $response = $signUp['response'];
        $response = $response->collect();

        $token = $response['authorization']['token'];

        $payload = [
            'post_name' => 'Tech post',
            'user_email' => 'example@example.com',
            'post_category' => '1',
            'file' => UploadedFile::fake()->image('avatar.jpg'),
        ];

        $response = $this->post('/api/post', $payload, [
            'Authorization' => 'Bearer '.$token,
        ]);

        $this->assertTrue($response->collect()->count() > 0);
    }

    public function test_if_user_can_see_its_posts()
    {
        $signUp = $this->signup();
        $response = $signUp['response'];
        $response = $response->collect();

        $token = $response['authorization']['token'];

        $payload = [
            'post_name' => 'Tech post',
            'user_email' => 'example@example.com',
            'post_category' => '1',
            'file' => UploadedFile::fake()->image('avatar.jpg'),
        ];

        $this->post('/api/post', $payload, [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response = $this->get('/api/post' . '?email=example@example.com', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $this->assertTrue($response->collect()->count() > 0);
    }
}
