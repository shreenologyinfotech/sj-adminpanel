<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Services;

use Illuminate\Support\Str;

/**
 * A minimal, dependency-free TOTP (RFC 6238) implementation.
 *
 * Deliberately avoids pulling in a third-party 2FA package: TOTP is a
 * small, stable, and well-specified algorithm, so implementing it
 * directly keeps the package's own dependency footprint small.
 */
class TwoFactorAuthenticationService
{
    protected const DIGITS = 6;
    protected const PERIOD = 30;
    protected const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public function generateSecretKey(int $length = 20): string
    {
        return $this->base32Encode(random_bytes($length));
    }

    /**
     * @return array<int, string> plain-text one-time recovery codes
     */
    public function generateRecoveryCodes(int $count = 8): array
    {
        return collect(range(1, $count))
            ->map(fn (): string => Str::random(10) . '-' . Str::random(10))
            ->values()
            ->all();
    }

    /**
     * otpauth:// URI suitable for rendering as a QR code by the frontend
     * (e.g. via a JS QR library, or an external QR image endpoint).
     */
    public function qrCodeUrl(string $secret, string $email, string $issuer): string
    {
        $label = rawurlencode("{$issuer}:{$email}");
        $params = http_build_query([
            'secret' => $secret,
            'issuer' => $issuer,
            'algorithm' => 'SHA1',
            'digits' => self::DIGITS,
            'period' => self::PERIOD,
        ]);

        return "otpauth://totp/{$label}?{$params}";
    }

    /**
     * Verifies a 6-digit code, tolerating +/- 1 time step of clock drift.
     */
    public function verify(string $secret, string $code, int $window = 1): bool
    {
        $code = preg_replace('/\s+/', '', $code);

        if (! is_string($code) || strlen($code) !== self::DIGITS) {
            return false;
        }

        $timestamp = (int) floor(time() / self::PERIOD);

        for ($step = -$window; $step <= $window; $step++) {
            if (hash_equals($this->codeAt($secret, $timestamp + $step), $code)) {
                return true;
            }
        }

        return false;
    }

    protected function codeAt(string $secret, int $timestamp): string
    {
        $key = $this->base32Decode($secret);
        $counter = pack('N*', 0) . pack('N*', $timestamp);

        $hash = hash_hmac('sha1', $counter, $key, true);
        $offset = ord($hash[19]) & 0x0F;

        $truncated = ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF);

        return str_pad((string) ($truncated % (10 ** self::DIGITS)), self::DIGITS, '0', STR_PAD_LEFT);
    }

    protected function base32Encode(string $binary): string
    {
        $bits = '';
        foreach (str_split($binary) as $char) {
            $bits .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }

        $output = '';
        foreach (str_split($bits, 5) as $chunk) {
            $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
            $output .= self::BASE32_ALPHABET[bindec($chunk)];
        }

        return $output;
    }

    protected function base32Decode(string $encoded): string
    {
        $encoded = strtoupper((string) preg_replace('/[^A-Z2-7]/', '', $encoded));

        $bits = '';
        foreach (str_split($encoded) as $char) {
            $position = strpos(self::BASE32_ALPHABET, $char);

            if ($position === false) {
                continue;
            }

            $bits .= str_pad(decbin($position), 5, '0', STR_PAD_LEFT);
        }

        $binary = '';
        foreach (str_split($bits, 8) as $byte) {
            if (strlen($byte) === 8) {
                $binary .= chr((int) bindec($byte));
            }
        }

        return $binary;
    }
}
