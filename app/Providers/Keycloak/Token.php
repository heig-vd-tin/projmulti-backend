<?php

namespace App\Providers\Keycloak;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * source: https://github.com/robsontenorio/laravel-keycloak-guard
 * adapté par Alec Berney
 */
class Token
{
    private const JWT_ALGORITHM = 'RS256';

    public static function decode(?string $publicKey, ?string $token): ?object
    {
        if (!$token || !$publicKey) {
            return null;
        }
        return JWT::decode($token, new Key(self::buildPublicKey($publicKey), self::JWT_ALGORITHM));
    }

    private static function buildPublicKey(string $key): string
    {
        return "-----BEGIN PUBLIC KEY-----\n" . wordwrap($key, 64, "\n", true) . "\n-----END PUBLIC KEY-----";
    }
}
