<?php

declare(strict_types=1);

namespace HuberCMS\Core;

use stdClass;
use RuntimeException;

/**
 * Jwt
 *
 * Minimal, dependency-free JWT implementation supporting HS256 (HMAC-SHA256).
 * Replaces firebase/php-jwt — no external package needed.
 *
 * Format:  base64url(header) . "." . base64url(payload) . "." . base64url(signature)
 * Signing: HMAC-SHA256( header + "." + payload, secret )
 *
 * @package HuberCMS\Core
 */
final class Jwt
{
    private const ALGORITHM = 'sha256';

    /**
     * Encodes a payload as a signed JWT string.
     *
     * @param array<string, mixed> $payload
     * @throws RuntimeException If secret is empty
     */
    public static function encode(array $payload, string $secret): string
    {
        if (empty($secret)) {
            throw new RuntimeException('JWT secret must not be empty.');
        }

        $header = self::base64UrlEncode(json_encode([
            'alg' => 'HS256',
            'typ' => 'JWT',
        ], JSON_THROW_ON_ERROR));

        $payload = self::base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));

        $signature = self::base64UrlEncode(
            hash_hmac(self::ALGORITHM, $header . '.' . $payload, $secret, true)
        );

        return $header . '.' . $payload . '.' . $signature;
    }

    /**
     * Decodes and validates a JWT string.
     *
     * @return stdClass Decoded payload
     * @throws RuntimeException On invalid format, bad signature, or expiration
     */
    public static function decode(string $token, string $secret): stdClass
    {
        if (empty($secret)) {
            throw new RuntimeException('JWT secret must not be empty.');
        }

        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new RuntimeException('Invalid JWT format: expected 3 segments.');
        }

        [$header64, $payload64, $signature64] = $parts;

        // Verify signature (timing-safe comparison)
        $expectedSignature = self::base64UrlEncode(
            hash_hmac(self::ALGORITHM, $header64 . '.' . $payload64, $secret, true)
        );

        if (!hash_equals($expectedSignature, $signature64)) {
            throw new RuntimeException('JWT signature verification failed.');
        }

        // Decode payload
        $payloadJson = self::base64UrlDecode($payload64);
        $payload = json_decode($payloadJson, false, 512, JSON_THROW_ON_ERROR);

        if (!($payload instanceof stdClass)) {
            throw new RuntimeException('JWT payload is not a valid JSON object.');
        }

        // Check expiration
        if (isset($payload->exp) && $payload->exp < time()) {
            throw new RuntimeException(
                sprintf('JWT expired at %s.', date('Y-m-d H:i:s', (int) $payload->exp))
            );
        }

        // Check not-before
        if (isset($payload->nbf) && $payload->nbf > time()) {
            throw new RuntimeException('JWT is not yet valid (nbf claim).');
        }

        return $payload;
    }

    // =========================================================
    // Base64url helpers (RFC 4648 §5 — no padding, URL-safe)
    // =========================================================

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string
    {
        $padded = strtr($data, '-_', '+/');
        $remainder = strlen($padded) % 4;

        if ($remainder !== 0) {
            $padded .= str_repeat('=', 4 - $remainder);
        }

        $decoded = base64_decode($padded, true);

        if ($decoded === false) {
            throw new RuntimeException('Failed to base64-decode JWT segment.');
        }

        return $decoded;
    }
}
