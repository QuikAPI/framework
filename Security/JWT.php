<?php
namespace QuikAPI\Security;

use Firebase\JWT\JWT as CoreJWT;
use Firebase\JWT\Key;
use DateTimeImmutable;

class JWT
{
    public static function encode(array $claims, ?string $ttl = null): string
    {
        $key = getenv('JWT_SECRET') ?: 'change-me';
        $alg = getenv('JWT_ALG') ?: 'HS256';
        $issuer = getenv('JWT_ISS') ?: 'quikapi';
        $now = new DateTimeImmutable();
        $payload = array_merge([
            'iss' => $issuer,
            'iat' => $now->getTimestamp(),
        ], $claims);
        if ($ttl) {
            $payload['exp'] = $now->modify($ttl)->getTimestamp();
        }
        return CoreJWT::encode($payload, $key, $alg);
    }

    public static function decode(string $token): array
    {
        $key = getenv('JWT_SECRET') ?: 'change-me';
        $alg = getenv('JWT_ALG') ?: 'HS256';
        $decoded = CoreJWT::decode($token, new Key($key, $alg));
        return json_decode(json_encode($decoded), true);
    }
}
