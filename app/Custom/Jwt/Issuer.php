<?php

namespace App\Custom\Jwt;

use DateTimeZone;
use Illuminate\Support\Facades\Log;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Token\Plain;

class Issuer
{
    private Configuration $config;

    private int $tokenTtl;

    private int $refreshTokenTtl;

    public function __construct()
    {
        $this->tokenTtl = env('JWT_TTL', 1);
        $this->refreshTokenTtl = env('JWT_REFRESH_TOKEN_TTL', 2);
        $this->setup();
    }

    private function setup(): void
    {
        $this->config = Config::get();
    }

    /**
     * @param  bool  $refreshToken
     * @return Plain|null
     */
    public function getToken(bool $refreshToken = false): Plain|null
    {
        $now = now()->toDateTimeImmutable();

        $tokenTtl = $this->tokenTtl;
        if ($refreshToken) {
            $tokenTtl = $this->refreshTokenTtl;
        }

        try {
            /* @var Plain $token */
            $token = $this->config->builder()
                // Configures the issuer (iss claim)
                ->issuedBy(env('JWT_ISSUED_BY'))
                // Configures the audience (aud claim)
                ->permittedFor(env('JWT_PERMITTED_FOR'))
                // Configures the id (jti claim)
                ->identifiedBy(env('JWT_IDENTIFIED_BY'))
                // Configures the time that the token was issue (iat claim)
                ->issuedAt($now)
                // Configures the time that the token can be used (nbf claim)
                // ->canOnlyBeUsedAfter($now->modify("+1 minute"))
                // Configures the expiration time of the token (exp claim)
                ->expiresAt($now->modify("+{$tokenTtl} minute"))
                // Configures a new claim, called "uid"
                ->withClaim('uid', 1)
                // Configures a new header, called "foo"
                ->withHeader('foo', 'bar')
                // Builds a new token
                ->getToken($this->config->signer(), $this->config->signingKey());
        } catch (\Throwable $e) {
            return null;
        }

        return $token;
    }

    /**
     * Generate the token and refresh token at the same time
     *
     * @param  bool  $onlyTheTokenStrings
     * @return array
     */
    public function getTokenPair(bool $onlyTheTokenStrings = false): array
    {
        $token = $this->getToken();
        $refreshToken = $this->getToken(true);

        if ($onlyTheTokenStrings) {
            $token = $token->toString();
            $refreshToken = $refreshToken->toString();
        }

        return [
            $token,
            $refreshToken,
        ];
    }
}
