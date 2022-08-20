<?php

namespace Custom\Jwt;

use App\Custom\Jwt\Config;
use App\Custom\Jwt\Issuer;
use Illuminate\Foundation\Testing\Concerns\InteractsWithTime;
use PHPUnit\Framework\TestCase;

class ValidationTest extends TestCase
{
    use InteractsWithTime;

    private int $tokenTtl;

    private int $refreshTokenTtl;

    public function setUp(): void
    {
        parent::setUp();

        $this->tokenTtl = env('TOKEN_TTL', 1);
        $this->refreshTokenTtl = env('REFRESH_TOKEN_TTL', 2);
    }

    public function test_if_the_token_is_expired()
    {
        $issuer = new Issuer();
        $config = Config::get();

        $token = $issuer->getToken();

        $token = $config->parser()->parse(
            $token->toString()
        );

        $this->travel($this->tokenTtl + 1)->minutes(function () use ($token) {
            $this->assertTrue($token->isExpired(now()));
        });
    }

    public function test_if_the_refresh_token_is_expired()
    {
        $issuer = new Issuer();
        $config = Config::get();

        $token = $issuer->getToken(true);

        $token = $config->parser()->parse(
            $token->toString()
        );

        $this->travel($this->refreshTokenTtl + 1)->minutes(function () use ($token) {
            $this->assertTrue($token->isExpired(now()));
        });
    }
}
