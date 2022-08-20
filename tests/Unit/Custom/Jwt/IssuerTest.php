<?php

namespace Custom\Jwt;

use App\Custom\Jwt\Issuer;
use PHPUnit\Framework\TestCase;

class IssuerTest extends TestCase
{
    public function test_if_it_can_generate_the_token()
    {
        $issuer = new Issuer();

        $token = $issuer->getToken();

        self::assertTrue($token != null);
    }

    public function test_if_it_can_generate_the_tokens_pair()
    {
        $issuer = new Issuer();

        self::assertTrue(count($issuer->getTokenPair()) == 2);
    }
}
